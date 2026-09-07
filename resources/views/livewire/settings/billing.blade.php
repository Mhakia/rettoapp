<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('settings_billing_title') }}</flux:heading>

    <x-settings.layout :heading="__('settings_billing_title')" :subheading="__('settings_billing_description')">
        @if (session('billingStatus'))
            <flux:callout variant="success" icon="check-circle" heading="{{ session('billingStatus') }}" class="mb-4" />
        @endif

        @if (session('billingError'))
            <flux:callout variant="danger" icon="x-circle" heading="{{ session('billingError') }}" class="mb-4" />
        @endif

        <div class="my-6 space-y-4">
            <flux:text>
                {{ $this->institution->subscribed() ? __('settings_subscription_active') : __('settings_subscription_inactive') }}
            </flux:text>

            @if ($this->institution->hasStripeId())
                <flux:button variant="primary" wire:click="manage">{{ __('settings_subscription_manage_button') }}</flux:button>
            @endif

            @if ($this->subscription && $this->wompiEnabled)
                <div class="space-y-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                    <flux:text>
                        {{ $this->subscription->wompi_payment_source_id
                            ? __('settings_billing_wompi_method_saved')
                            : __('settings_billing_wompi_no_method') }}
                    </flux:text>

                    <form method="POST" action="{{ route('billing.wompi.payment-source') }}">
                        @csrf
                        <script
                            src="https://checkout.wompi.co/widget.js"
                            data-render="button"
                            data-widget-operation="tokenize"
                            data-public-key="{{ config('services.wompi.public_key') }}"
                        ></script>
                    </form>
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
