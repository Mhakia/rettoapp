<?php

namespace App\Livewire\Billing;

use App\Models\Invoice;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Facturas')]
class InvoicesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public ?int $detailInvoiceId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Invoice::class);
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
    public function invoices()
    {
        $search = trim($this->search);

        return Invoice::query()
            ->with(['institution', 'subscription'])
            ->when($search !== '', fn ($query) => $query->where('number', 'ilike', "%{$search}%")
                ->orWhereHas('institution', fn ($q) => $q->where('name', 'ilike', "%{$search}%")))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function detailInvoice()
    {
        if (! $this->detailInvoiceId) {
            return null;
        }

        return Invoice::with(['items', 'subscription.institution'])
            ->find($this->detailInvoiceId);
    }

    public function closeDetail(): void
    {
        $this->detailInvoiceId = null;
    }

    public function render()
    {
        return view('livewire.billing.invoices-index');
    }
}
