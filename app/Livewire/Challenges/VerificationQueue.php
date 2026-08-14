<?php

namespace App\Livewire\Challenges;

use App\Models\ChallengeCompletion;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Verificar retos')]
class VerificationQueue extends Component
{
    #[Computed]
    public function pending()
    {
        $groupIds = Auth::user()->teacherGroups()->pluck('groups.id');

        return ChallengeCompletion::with(['challenge', 'user', 'membership'])
            ->where('status', 'submitted')
            ->whereHas('challenge', fn ($query) => $query->where('target_role', 'student'))
            ->whereHas('membership', fn ($query) => $query->whereIn('group_id', $groupIds))
            ->latest('submitted_at')
            ->get();
    }

    public function verify(int $completionId): void
    {
        $this->decide($completionId, 'verified');
    }

    public function reject(int $completionId): void
    {
        $this->decide($completionId, 'rejected');
    }

    protected function decide(int $completionId, string $decision): void
    {
        $completion = ChallengeCompletion::findOrFail($completionId);

        $this->authorize('verify', $completion);

        $completion->update([
            'status' => $decision,
            'points_earned' => $decision === 'verified' ? $completion->challenge->points : null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        unset($this->pending);

        Flux::toast(variant: 'success', text: $decision === 'verified' ? __('Reto verificado.') : __('Reto rechazado.'));
    }

    public function render()
    {
        return view('livewire.challenges.verification-queue');
    }
}
