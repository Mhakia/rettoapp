<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Suscripción') }}</flux:heading>

    <x-settings.layout :heading="__('Suscripción')" :subheading="__('Gestiona el plan de tu institución')">
        <div class="my-6 space-y-4">
            <flux:text>
                {{ $this->institution->subscribed() ? __('Tu institución tiene una suscripción activa.') : __('Tu institución no tiene una suscripción activa.') }}
            </flux:text>

            @if ($this->institution->hasStripeId())
                <flux:button variant="primary" wire:click="manage">{{ __('Gestionar suscripción') }}</flux:button>
            @endif
        </div>
    </x-settings.layout>
</section>
