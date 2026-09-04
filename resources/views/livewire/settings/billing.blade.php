<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('settings_billing_title') }}</flux:heading>

    <x-settings.layout :heading="__('settings_billing_title')" :subheading="__('settings_billing_description')">
        <div class="my-6 space-y-4">
            <flux:text>
                {{ $this->institution->subscribed() ? __('settings_subscription_active') : __('settings_subscription_inactive') }}
            </flux:text>

            @if ($this->institution->hasStripeId())
                <flux:button variant="primary" wire:click="manage">{{ __('settings_subscription_manage_button') }}</flux:button>
            @endif
        </div>
    </x-settings.layout>
</section>
