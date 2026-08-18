<?php

namespace App\Livewire\Actors;

use App\Models\InstitutionMembership;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class WithdrawMembership extends Component
{
    #[Locked]
    public int $membershipId;

    public string $reason = '';

    public function mount(InstitutionMembership $membership): void
    {
        $this->membershipId = $membership->id;
    }

    public function withdraw(): void
    {
        $membership = InstitutionMembership::findOrFail($this->membershipId);

        $this->authorize('withdraw', $membership);

        $this->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($membership) {
            $membership->update([
                'status' => 'withdrawn',
                'ended_at' => now(),
                'reason' => $this->reason,
            ]);

            if ($membership->user->hasRole('teacher')) {
                $membership->user->teacherGroups()
                    ->wherePivot('institution_membership_id', $membership->id)
                    ->detach();
            }
        });

        Flux::toast(variant: 'success', text: __('Matrícula cerrada.'));
        $this->reset('reason');
        $this->dispatch('modal-close', name: "withdraw-{$this->membershipId}");
        $this->dispatch('membership-withdrawn');
    }

    public function render()
    {
        return view('livewire.actors.withdraw-membership');
    }
}
