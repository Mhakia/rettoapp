<?php

namespace App\Livewire\Actors;

use App\Models\Group;
use App\Models\InstitutionMembership;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ManageMembershipGroups extends Component
{
    #[Locked]
    public int $membershipId;

    #[Locked]
    public bool $isTeacher;

    public ?int $group_id = null;

    /**
     * @var array<int, int>
     */
    public array $group_ids = [];

    public function mount(InstitutionMembership $membership): void
    {
        $this->membershipId = $membership->id;
        $this->isTeacher = $membership->user->hasRole('teacher');

        if ($this->isTeacher) {
            $this->group_ids = $membership->user->teacherGroups()
                ->wherePivot('institution_membership_id', $membership->id)
                ->pluck('groups.id')
                ->all();
        } else {
            $this->group_id = $membership->group_id;
        }
    }

    #[Computed]
    public function membership(): InstitutionMembership
    {
        return InstitutionMembership::with('institution')->findOrFail($this->membershipId);
    }

    #[Computed]
    public function groups()
    {
        return Group::where('institution_id', $this->membership->institution_id)->orderBy('name')->get();
    }

    public function save(): void
    {
        $membership = $this->membership;

        $this->authorize('manageActors', $membership->institution);

        if ($this->isTeacher) {
            $data = $this->validate([
                'group_ids' => ['array'],
                'group_ids.*' => ['integer', Rule::exists('groups', 'id')->where('institution_id', $membership->institution_id)],
            ]);

            $currentIds = $membership->user->teacherGroups()
                ->wherePivot('institution_membership_id', $membership->id)
                ->pluck('groups.id')
                ->all();

            $toAttach = array_values(array_diff($data['group_ids'], $currentIds));
            $toDetach = array_values(array_diff($currentIds, $data['group_ids']));

            if ($toDetach !== []) {
                $membership->user->teacherGroups()
                    ->wherePivot('institution_membership_id', $membership->id)
                    ->detach($toDetach);
            }

            if ($toAttach !== []) {
                $membership->user->teacherGroups()->attach(
                    collect($toAttach)->mapWithKeys(fn ($id) => [$id => ['institution_membership_id' => $membership->id]])->all()
                );
            }

            Flux::toast(variant: 'success', text: __('Grupos del profesor actualizados.'));
        } else {
            $data = $this->validate([
                'group_id' => ['required', Rule::exists('groups', 'id')->where('institution_id', $membership->institution_id)],
            ]);

            $membership->update(['group_id' => $data['group_id']]);

            Flux::toast(variant: 'success', text: __('Grupo del estudiante actualizado.'));
        }

        $this->dispatch('modal-close', name: "manage-groups-{$this->membershipId}");
        $this->dispatch('membership-groups-updated');
    }

    public function render()
    {
        return view('livewire.actors.manage-membership-groups');
    }
}
