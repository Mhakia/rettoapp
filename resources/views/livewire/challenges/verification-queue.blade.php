<section class="w-full">
    <flux:heading size="lg">{{ __('challenge_queue_title') }}</flux:heading>

    <div class="mt-6 divide-y">
        @forelse ($this->pending as $completion)
            <div wire:key="completion-{{ $completion->id }}" class="flex items-center justify-between py-4">
                <div>
                    <flux:text class="font-medium">{{ $completion->challenge->title }}</flux:text>
                    <flux:text class="text-sm text-gray-500">{{ $completion->user->name }} · {{ $completion->submitted_at?->diffForHumans() }}</flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:button size="sm" variant="primary" wire:click="verify({{ $completion->id }})">{{ __('challenge_queue_verify') }}</flux:button>
                    <flux:button size="sm" variant="danger" wire:click="reject({{ $completion->id }})">{{ __('challenge_queue_reject') }}</flux:button>
                </div>
            </div>
        @empty
            <flux:text class="py-4">{{ __('challenge_queue_no_pending') }}</flux:text>
        @endforelse
    </div>
</section>
