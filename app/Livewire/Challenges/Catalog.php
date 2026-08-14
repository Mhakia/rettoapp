<?php

namespace App\Livewire\Challenges;

use App\Models\Challenge;
use App\Models\ChallengeCompletion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
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

    #[Computed]
    public function challenges()
    {
        $user = Auth::user();

        return Challenge::visibleTo($user)
            ->with(['completions' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByDesc('starts_at')
            ->get();
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

        ChallengeCompletion::create([
            'challenge_id' => $challenge->id,
            'institution_membership_id' => $user->activeMembership?->id,
            'user_id' => $user->id,
            'status' => $selfReported ? 'verified' : 'submitted',
            'evidence_path' => $evidencePath,
            'points_earned' => $selfReported ? $challenge->points : null,
            'submitted_at' => now(),
            'verified_at' => $selfReported ? now() : null,
        ]);

        $this->reset(['submittingChallengeId', 'evidence']);
        unset($this->challenges);

        Flux::toast(variant: 'success', text: __('Reto enviado.'));
    }

    public function render()
    {
        return view('livewire.challenges.catalog');
    }
}
