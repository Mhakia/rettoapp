<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeCompletion;
use App\Models\ChallengeView;
use Flux\Flux;
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

    public mixed $evidence = null;

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

        $this->validate([
            'evidence' => ['nullable', 'file', 'max:10240'],
        ]);

        $evidencePath = $this->evidence
            ? Storage::disk('s3')->putFile('challenge-evidence', $this->evidence)
            : null;

        $selfReported = in_array($challenge->target_role, ['teacher', 'guardian']);

        $startedAt = ChallengeView::where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->value('started_at');

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

        $this->reset(['submittingChallengeId', 'evidence']);
        unset($this->challenges);

        // A class-session login is scoped to one specific challenge: close it automatically once answered.
        if (session('locked_challenge_ulid') === $challenge->ulid) {
            Flux::toast(variant: 'success', text: __('Reto enviado. ¡Listo!'));

            Auth::guard('web')->logout();
            Session::invalidate();
            Session::regenerateToken();

            $this->redirect(route('class-sessions.join'), navigate: true);

            return;
        }

        Flux::toast(variant: 'success', text: __('Reto enviado.'));
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
