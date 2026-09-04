<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white px-6 py-5">
        <div>
            <flux:heading size="xl">{{ __('Facturas') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Historial de facturas generadas por el sistema.') }}</flux:text>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por número o institución...')" class="max-w-md" />

        <flux:select wire:model.live="status" class="max-w-xs">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            <flux:select.option value="draft">{{ __('Borrador') }}</flux:select.option>
            <flux:select.option value="sent">{{ __('Enviada') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Pagada') }}</flux:select.option>
            <flux:select.option value="cancelled">{{ __('Cancelada') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="divide-y divide-zinc-100">
            @forelse ($this->invoices as $invoice)
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <flux:heading size="sm">{{ $invoice->number }}</flux:heading>
                            <span class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-blue-100 text-blue-800': '{{ $invoice->status }}' === 'draft',
                                    'bg-yellow-100 text-yellow-800': '{{ $invoice->status }}' === 'sent',
                                    'bg-green-100 text-green-800': '{{ $invoice->status }}' === 'paid',
                                    'bg-red-100 text-red-800': '{{ $invoice->status }}' === 'cancelled',
                                }">
                                {{ __($invoice->status) }}
                            </span>
                        </div>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ $invoice->institution->name }} · {{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}
                        </flux:text>
                        <flux:text class="text-sm font-semibold">
                            {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                        </flux:text>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('view', $invoice)
                            <flux:button size="sm" icon="eye" :tooltip="__('Ver detalle')" wire:click="$set('detailInvoiceId', {{ $invoice->id }})" />
                        @endcan
                    </div>
                </div>
            @empty
                <flux:text class="block px-6 py-10 text-center text-brand-text-muted!">{{ __('No se encontraron facturas.') }}</flux:text>
            @endforelse
        </div>
    </div>

    @if ($this->invoices->hasPages())
        <div class="mt-6">
            {{ $this->invoices->links() }}
        </div>
    @endif

    @if ($this->detailInvoice)
        <flux:modal name="invoice-detail" :dismissible="false" class="w-full max-w-2xl">
            <div class="space-y-6">
                <div class="border-b pb-4">
                    <flux:heading size="lg">{{ $this->detailInvoice->number }}</flux:heading>
                    <flux:text class="text-brand-text-muted!">{{ $this->detailInvoice->institution->name }}</flux:text>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('Período') }}</flux:text>
                        <flux:text>{{ $this->detailInvoice->period_start->format('d/m/Y') }} - {{ $this->detailInvoice->period_end->format('d/m/Y') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('Estado') }}</flux:text>
                        <flux:text>{{ __($this->detailInvoice->status) }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('Método de pago') }}</flux:text>
                        <flux:text>{{ __($this->detailInvoice->payment_method) }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('Total') }}</flux:text>
                        <flux:text class="text-lg font-bold">{{ number_format($this->detailInvoice->total, 2) }} {{ $this->detailInvoice->currency }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:text class="mb-3 text-xs font-semibold uppercase text-brand-text-muted!">{{ __('Líneas de factura') }}</flux:text>
                    <div class="divide-y rounded-lg border border-zinc-200">
                        @forelse ($this->detailInvoice->items as $item)
                            <div class="flex justify-between px-4 py-3">
                                <div class="flex-1">
                                    <flux:text class="font-medium">{{ $item->description }}</flux:text>
                                </div>
                                <flux:text class="font-semibold">{{ number_format($item->amount, 2) }}</flux:text>
                            </div>
                        @empty
                            <flux:text class="block px-4 py-6 text-center text-sm text-brand-text-muted!">{{ __('Sin líneas') }}</flux:text>
                        @endforelse
                    </div>
                </div>

                <div class="flex gap-3 border-t pt-4">
                    <flux:button wire:click="closeDetail()" variant="subtle">{{ __('Cerrar') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</section>
