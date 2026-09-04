@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="__('app_name')" {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="size-8 rounded-full object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="__('app_name')" {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="size-8 rounded-full object-contain" />
        </x-slot>
    </flux:brand>
@endif
