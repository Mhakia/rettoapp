<section class="w-full">
    <flux:heading size="lg">{{ __('Retos por verificar') }}</flux:heading>

    <div class="mt-6 divide-y">
        @forelse ($this->pending as $completion)
            <div class="flex items-center justify-between py-4">
                <div>
                    <flux:text class="font-medium">{{ $completion->challenge->title }}</flux:text>
                    <flux:text class="text-sm text-gray-500">{{ $completion->user->name }} · {{ $completion->submitted_at?->diffForHumans() }}</flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:button size="sm" variant="primary" wire:click="verify({{ $completion->id }})">{{ __('Verificar') }}</flux:button>
                    <flux:button size="sm" variant="danger" wire:click="reject({{ $completion->id }})">{{ __('Rechazar') }}</flux:button>
                </div>
            </div>
        @empty
            <flux:text class="py-4">{{ __('No hay retos pendientes de verificación.') }}</flux:text>
        @endforelse
    </div>
</section>
