<section class="mx-auto w-full max-w-6xl pb-16">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('billing_invoices_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('billing_invoices_description') }}</flux:text>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-3 border-b border-zinc-100 p-4 dark:border-zinc-800">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('billing_invoices_search_placeholder')" class="min-w-64 grow" />

            <flux:select wire:model.live="status" class="w-44">
                <flux:select.option value="">{{ __('billing_status_all') }}</flux:select.option>
                <flux:select.option value="draft">{{ __('billing_status_draft') }}</flux:select.option>
                <flux:select.option value="sent">{{ __('billing_status_sent') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('billing_status_paid') }}</flux:select.option>
                <flux:select.option value="cancelled">{{ __('billing_status_cancelled') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 text-left text-xs font-semibold text-brand-text-muted uppercase dark:border-zinc-800">
                        <th class="px-4 py-3">{{ __('billing_table_number') }}</th>
                        <th class="px-4 py-3">{{ __('billing_table_institution') }}</th>
                        <th class="px-4 py-3">{{ __('billing_period') }}</th>
                        <th class="px-4 py-3">{{ __('field_status') }}</th>
                        <th class="px-4 py-3">{{ __('billing_total') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('challenge_table_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->invoices as $invoice)
                        <tr wire:key="invoice-{{ $invoice->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 font-mono text-xs font-bold text-teal-deep dark:text-teal">{{ $invoice->number }}</td>
                            <td class="px-4 py-3 text-brand-text">{{ $invoice->institution->name }}</td>
                            <td class="px-4 py-3 text-brand-text-muted!">{{ $invoice->period_start->format('d/m/Y') }} - {{ $invoice->period_end->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" :color="match ($invoice->status) {
                                    'draft' => 'zinc',
                                    'sent' => 'blue',
                                    'paid' => 'teal',
                                    'cancelled' => 'red',
                                }">
                                    {{ __($invoice->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 font-semibold text-brand-text">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('view', $invoice)
                                        <flux:button size="sm" icon="eye" :tooltip="__('action_view_details')" wire:click="$set('detailInvoiceId', {{ $invoice->id }})" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-brand-text-muted!">{{ __('billing_invoices_no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->invoices->hasPages())
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                {{ $this->invoices->links() }}
            </div>
        @endif
    </div>

    @if ($this->detailInvoice)
        <flux:modal name="invoice-detail" :dismissible="false" class="w-full max-w-2xl">
            <div class="space-y-6">
                <div class="border-b border-zinc-100 pb-4 dark:border-zinc-800">
                    <flux:heading size="lg" class="text-teal-deep!">{{ $this->detailInvoice->number }}</flux:heading>
                    <flux:text class="text-brand-text-muted!">{{ $this->detailInvoice->institution->name }}</flux:text>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('billing_period') }}</flux:text>
                        <flux:text>{{ $this->detailInvoice->period_start->format('d/m/Y') }} - {{ $this->detailInvoice->period_end->format('d/m/Y') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('field_status') }}</flux:text>
                        <flux:text>{{ __($this->detailInvoice->status) }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('billing_payment_method') }}</flux:text>
                        <flux:text>{{ __($this->detailInvoice->payment_method) }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs font-semibold uppercase text-brand-text-muted!">{{ __('billing_total') }}</flux:text>
                        <flux:text class="text-lg font-bold">{{ number_format($this->detailInvoice->total, 2) }} {{ $this->detailInvoice->currency }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:text class="mb-3 text-xs font-semibold uppercase text-brand-text-muted!">{{ __('billing_invoice_lines') }}</flux:text>
                    <div class="divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                        @forelse ($this->detailInvoice->items as $item)
                            <div class="flex justify-between px-4 py-3">
                                <div class="flex-1">
                                    <flux:text class="font-medium">{{ $item->description }}</flux:text>
                                </div>
                                <flux:text class="font-semibold">{{ number_format($item->amount, 2) }}</flux:text>
                            </div>
                        @empty
                            <flux:text class="block px-4 py-6 text-center text-sm text-brand-text-muted!">{{ __('billing_invoice_no_items') }}</flux:text>
                        @endforelse
                    </div>
                </div>

                <div class="flex gap-3 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <flux:button wire:click="closeDetail()" variant="subtle">{{ __('action_close') }}</flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</section>

