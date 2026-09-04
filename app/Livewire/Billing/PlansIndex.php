<?php

namespace App\Livewire\Billing;

use App\Models\Plan;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Planes de suscripción')]
class PlansIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public bool $showForm = false;

    public ?Plan $editingPlan = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Plan::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function plans()
    {
        $search = trim($this->search);

        return Plan::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'ilike', "%{$search}%")
                ->orWhere('slug', 'ilike', "%{$search}%"))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function createPlan(): void
    {
        $this->authorize('create', Plan::class);
        $this->editingPlan = null;
        $this->showForm = true;
    }

    public function editPlan(Plan $plan): void
    {
        $this->authorize('update', $plan);
        $this->editingPlan = $plan;
        $this->showForm = true;
    }

    public function deletePlan(Plan $plan): void
    {
        $this->authorize('delete', $plan);
        $plan->delete();
        Flux::toast()->success('Plan eliminado.');
    }

    public function closePlanForm(): void
    {
        $this->showForm = false;
        $this->editingPlan = null;
    }

    public function render()
    {
        return view('livewire.billing.plans-index');
    }
}
