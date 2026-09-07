<section class="mx-auto w-full max-w-6xl pb-16">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('billing_plans_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('billing_plans_description') }}</flux:text>
        </div>

        @can('create', \App\Models\Plan::class)
            <flux:button variant="primary" icon="plus" class="bg-teal! hover:bg-teal-deep!" wire:click="createPlan">
                {{ __('billing_plan_create_button') }}
            </flux:button>
        @endcan
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-100 p-4 dark:border-zinc-800">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('billing_plans_search_placeholder')" class="max-w-md" />
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 text-left text-xs font-semibold text-brand-text-muted uppercase dark:border-zinc-800">
                        <th class="px-4 py-3">{{ __('billing_table_plan') }}</th>
                        <th class="px-4 py-3">{{ __('billing_plan_base_price') }}</th>
                        <th class="px-4 py-3">{{ __('billing_plan_included_students') }}</th>
                        <th class="px-4 py-3">{{ __('billing_plan_extra_student_price') }}</th>
                        <th class="px-4 py-3">{{ __('billing_table_cycle') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('challenge_table_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <div class="font-medium text-brand-text">{{ $plan->name }}</div>
                                <div class="text-xs text-brand-text-muted!">{{ $plan->description }}</div>
                            </td>
                            <td class="px-4 py-3 text-brand-text">{{ number_format($plan->base_price, 2) }}</td>
                            <td class="px-4 py-3 text-brand-text">{{ $plan->included_students }}</td>
                            <td class="px-4 py-3 text-brand-text">{{ number_format($plan->price_per_extra_student, 2) }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="zinc">{{ __('frequency_'.$plan->billing_cycle) }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $plan)
                                        <flux:button size="sm" icon="pencil-square" :tooltip="__('action_edit')" wire:click="editPlan('{{ $plan->uuid }}')" />
                                    @endcan

                                    @can('delete', $plan)
                                        <flux:button size="sm" variant="danger" icon="trash" :tooltip="__('action_delete')" wire:click="deletePlan('{{ $plan->uuid }}')" wire:confirm="{{ __('billing_plan_confirm_delete') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-brand-text-muted!">{{ __('billing_plans_no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->plans->hasPages())
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                {{ $this->plans->links() }}
            </div>
        @endif
    </div>

    <flux:modal name="plan-form" :dismissible="false" class="w-full max-w-2xl">
        <flux:heading class="text-teal-deep!">{{ $this->editingPlan ? __('billing_plan_edit_title') : __('billing_plan_create_title') }}</flux:heading>
        <livewire:billing.plan-form :plan="$this->editingPlan" wire:key="plan-form-{{ $this->editingPlan?->id ?? 'new' }}" />
    </flux:modal>
</section>

