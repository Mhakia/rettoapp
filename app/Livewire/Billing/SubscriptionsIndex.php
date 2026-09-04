<?php

namespace App\Livewire\Billing;

use App\Models\InstitutionSubscription;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Suscripciones de instituciones')]
class SubscriptionsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public bool $showForm = false;

    public ?InstitutionSubscription $editingSubscription = null;

    public function mount(): void
    {
        $this->authorize('viewAny', InstitutionSubscription::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function subscriptions()
    {
        $search = trim($this->search);

        return InstitutionSubscription::query()
            ->with('institution')
            ->when($search !== '', fn ($query) => $query->whereHas('institution', fn ($q) => $q->where('name', 'ilike', "%{$search}%")))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    public function createSubscription(): void
    {
        $this->authorize('create', InstitutionSubscription::class);
        $this->editingSubscription = null;
        $this->showForm = true;
    }

    public function editSubscription(InstitutionSubscription $subscription): void
    {
        $this->authorize('update', $subscription);
        $this->editingSubscription = $subscription;
        $this->showForm = true;
    }

    public function deleteSubscription(InstitutionSubscription $subscription): void
    {
        $this->authorize('delete', $subscription);
        $subscription->delete();
        Flux::toast()->success('Suscripción eliminada.');
    }

    public function closeSubscriptionForm(): void
    {
        $this->showForm = false;
        $this->editingSubscription = null;
    }

    public function render()
    {
        return view('livewire.billing.subscriptions-index');
    }
}
