<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white px-6 py-5">
        <div>
            <flux:heading size="xl">{{ __('billing_subscriptions_title') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('billing_subscriptions_description') }}</flux:text>
        </div>

        @can('create', \App\Models\InstitutionSubscription::class)
            <flux:button variant="primary" icon="plus" href="#" wire:click="createSubscription">{{ __('billing_subscription_create_button') }}</flux:button>
        @endcan
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('billing_subscriptions_search_placeholder')" class="max-w-md" />

        <flux:select wire:model.live="status" class="max-w-xs">
            <flux:select.option value="">{{ __('billing_status_all') }}</flux:select.option>
            <flux:select.option value="active">{{ __('subscription_status_active') }}</flux:select.option>
            <flux:select.option value="inactive">{{ __('subscription_status_inactive') }}</flux:select.option>
            <flux:select.option value="paused">{{ __('subscription_status_paused') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm">
        <div class="divide-y divide-zinc-100">
            @forelse ($this->subscriptions as $subscription)
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50">
                    <div class="flex-1">
                        <flux:heading size="sm">{{ $subscription->institution->name }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ __('billing_subscription_summary', [
                                'amount' => number_format($subscription->base_price, 2),
                                'count' => $subscription->included_students,
                                'price' => number_format($subscription->price_per_extra_student, 2),
                            ]) }}
                        </flux:text>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-800': '{{ $subscription->status }}' === 'active',
                                    'bg-gray-100 text-gray-800': '{{ $subscription->status }}' === 'inactive',
                                    'bg-yellow-100 text-yellow-800': '{{ $subscription->status }}' === 'paused',
                                }">
                                {{ __($subscription->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('update', $subscription)
                            <flux:button size="sm" icon="pencil-square" :tooltip="__('action_edit')" wire:click="editSubscription({{ $subscription->id }})" />
                        @endcan

                        @can('delete', $subscription)
                            <flux:button size="sm" variant="danger" icon="trash" :tooltip="__('action_delete')" wire:click="deleteSubscription({{ $subscription->id }})" wire:confirm="{{ __('billing_subscription_confirm_delete') }}" />
                        @endcan
                    </div>
                </div>
            @empty
                <flux:text class="block px-6 py-10 text-center text-brand-text-muted!">{{ __('billing_subscriptions_no_results') }}</flux:text>
            @endforelse
        </div>
    </div>

    @if ($this->subscriptions->hasPages())
        <div class="mt-6">
            {{ $this->subscriptions->links() }}
        </div>
    @endif

    @if ($this->showForm)
        <flux:modal name="subscription-form" wire:click="closeSubscriptionForm()" :dismissible="false">
            <flux:heading>{{ $this->editingSubscription ? __('billing_subscription_edit_title') : __('billing_subscription_create_button') }}</flux:heading>
            <livewire:billing.subscription-form :subscription="$this->editingSubscription" wire:key="subscription-form-{{ $this->editingSubscription?->id ?? 'new' }}" />
        </flux:modal>
    @endif
</section>
