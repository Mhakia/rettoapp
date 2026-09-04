<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('settings_appearance_title') }}</flux:heading>

    <x-settings.layout :heading="__('settings_appearance_title')" :subheading="__('settings_appearance_description')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('appearance_light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('appearance_dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('appearance_system') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
