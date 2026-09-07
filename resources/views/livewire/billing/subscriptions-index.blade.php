<section class="mx-auto w-full max-w-6xl pb-16">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('billing_subscriptions_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('billing_subscriptions_description') }}</flux:text>
        </div>

        @can('create', \App\Models\InstitutionSubscription::class)
            <flux:button variant="primary" icon="plus" class="bg-teal! hover:bg-teal-deep!" href="{{ route('billing.subscriptions.create') }}" wire:navigate>
                {{ __('billing_subscription_create_button') }}
            </flux:button>
        @endcan
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-3 border-b border-zinc-100 p-4 dark:border-zinc-800">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('billing_subscriptions_search_placeholder')" class="min-w-64 grow" />

            <flux:select wire:model.live="status" class="w-44">
                <flux:select.option value="">{{ __('billing_status_all') }}</flux:select.option>
                <flux:select.option value="active">{{ __('subscription_status_active') }}</flux:select.option>
                <flux:select.option value="inactive">{{ __('subscription_status_inactive') }}</flux:select.option>
                <flux:select.option value="paused">{{ __('subscription_status_paused') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-100 text-left text-xs font-semibold text-brand-text-muted uppercase dark:border-zinc-800">
                        <th class="px-4 py-3">{{ __('billing_table_institution') }}</th>
                        <th class="px-4 py-3">{{ __('billing_plan_base_price') }}</th>
                        <th class="px-4 py-3">{{ __('billing_plan_included_students') }}</th>
                        <th class="px-4 py-3">{{ __('billing_plan_extra_student_price') }}</th>
                        <th class="px-4 py-3">{{ __('field_status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('challenge_table_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->subscriptions as $subscription)
                        <tr wire:key="subscription-{{ $subscription->id }}" class="transition hover:bg-zinc-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 font-medium text-brand-text">{{ $subscription->institution->name }}</td>
                            <td class="px-4 py-3 text-brand-text">{{ number_format($subscription->base_price, 2) }}</td>
                            <td class="px-4 py-3 text-brand-text">{{ $subscription->included_students }}</td>
                            <td class="px-4 py-3 text-brand-text">{{ number_format($subscription->price_per_extra_student, 2) }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" :color="match ($subscription->status) {
                                    'active' => 'teal',
                                    'inactive' => 'zinc',
                                    'paused' => 'amber',
                                    default => 'red',
                                }">
                                    {{ __('subscription_status_'.$subscription->status) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $subscription)
                                        <flux:button size="sm" icon="pencil-square" :tooltip="__('action_edit')" href="{{ route('billing.subscriptions.edit', $subscription->uuid) }}" wire:navigate />
                                    @endcan

                                    @can('delete', $subscription)
                                        <flux:button size="sm" variant="danger" icon="trash" :tooltip="__('action_delete')" wire:click="deleteSubscription('{{ $subscription->uuid }}')" wire:confirm="{{ __('billing_subscription_confirm_delete') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-brand-text-muted!">{{ __('billing_subscriptions_no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->subscriptions->hasPages())
            <div class="border-t border-zinc-100 p-4 dark:border-zinc-800">
                {{ $this->subscriptions->links() }}
            </div>
        @endif
    </div>
</section>

