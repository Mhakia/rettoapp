@props(['heading', 'items'])

<flux:sidebar.group :heading="$heading" class="grid">
    @foreach ($items as $item)
        <flux:sidebar.item icon="{{ $item['icon'] }}" :href="route($item['route'])" :current="request()->routeIs($item['current'])" wire:navigate>
            {{ $item['label'] }}
        </flux:sidebar.item>
    @endforeach
</flux:sidebar.group>
