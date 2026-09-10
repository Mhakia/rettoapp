<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeCompletion;
use App\Models\ChallengeView;
use Flux\Flux;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Retos')]
class Catalog extends Component
{
    use WithFileUploads;

    public ?int $submittingChallengeId = null;

    /** Keyed by challenge ulid so a file picked for one card never leaks into another. */
    public array $evidence = [];

    /**
     * Stamps a first-view timestamp for each visible challenge (separate from
     * challenge_completions, so it never affects the existing status-based counts elsewhere).
     */
    public function mount(): void
    {
        $user = Auth::user();

        $alreadyTracked = ChallengeView::where('user_id', $user->id)->pluck('challenge_id');

        Challenge::visibleTo($user)
            ->whereNotIn('id', $alreadyTracked)
            ->get(['id'])
            ->each(fn (Challenge $challenge) => ChallengeView::create([
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'started_at' => now(),
            ]));
    }

    #[Computed]
    public function challenges()
    {
        $user = Auth::user();

        $query = Challenge::visibleTo($user)
            ->withCount('questions')
            ->with(['completions' => fn ($query) => $query->where('user_id', $user->id)]);

        if ($lockedUlid = session('locked_challenge_ulid')) {
            $query->where('ulid', $lockedUlid);
        }

        return $query->orderByDesc('starts_at')->get();
    }

    public function complete(string $challengeUlid): void
    {
        $user = Auth::user();
        $challenge = Challenge::where('ulid', $challengeUlid)->firstOrFail();

        $this->authorize('complete', $challenge);

        // Retos con preguntas se responden por pregunta (no implementado aún); bloquear el formulario genérico.
        if ($challenge->questions()->exists()) {
            Flux::toast(variant: 'danger', text: __('challenge_has_questions_blocked'));

            return;
        }

        $selfReported = in_array($challenge->target_role, ['teacher', 'guardian']);

        // Sin membresía activa, la completión quedaría invisible para siempre en la cola de verificación.
        if (! $selfReported && ! $user->activeMembership) {
            Flux::toast(variant: 'danger', text: __('challenge_no_active_membership'));

            return;
        }

        // Defensa contra doble clic/doble envío (además del índice único en BD): si ya existe,
        // saltar validación/subida/creación pero seguir el mismo flujo de éxito de abajo.
        $alreadyCompleted = ChallengeCompletion::where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $alreadyCompleted) {
            $this->validate([
                "evidence.{$challengeUlid}" => ['nullable', 'file', 'max:10240'],
            ]);

            $evidenceFile = $this->evidence[$challengeUlid] ?? null;

            $evidencePath = $evidenceFile
                ? Storage::disk('s3')->putFile('challenge-evidence', $evidenceFile)
                : null;

            $startedAt = ChallengeView::where('challenge_id', $challenge->id)
                ->where('user_id', $user->id)
                ->value('started_at');

            try {
                ChallengeCompletion::create([
                    'challenge_id' => $challenge->id,
                    'institution_membership_id' => $user->activeMembership?->id,
                    'user_id' => $user->id,
                    'status' => $selfReported ? 'verified' : 'submitted',
                    'evidence_path' => $evidencePath,
                    'points_earned' => $selfReported ? $challenge->points : null,
                    'started_at' => $startedAt,
                    'submitted_at' => now(),
                    'origin' => session('challenge_origin'),
                    'verified_at' => $selfReported ? now() : null,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Otra petición ganó la carrera y ya la creó: borrar el archivo huérfano que subimos de más.
                if ($evidencePath) {
                    Storage::disk('s3')->delete($evidencePath);
                }
            }
        }

        unset($this->evidence[$challengeUlid]);
        $this->reset(['submittingChallengeId']);
        unset($this->challenges);

        // A class-session login is scoped to one specific challenge: close it automatically once answered.
        if (session('locked_challenge_ulid') === $challenge->ulid) {
            Flux::toast(variant: 'success', text: __('challenge_submitted_success'));

            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();

            $this->redirect(route('class-sessions.join'), navigate: true);

            return;
        }

        Flux::toast(variant: 'success', text: __('challenge_submitted'));
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();

        $this->redirect(route('class-sessions.join'), navigate: true);
    }

    public function render()
    {
        return view('livewire.challenges.catalog')
            ->layout(session('student_access_mode') ? 'layouts.student-locked' : 'layouts.app');
    }
}
