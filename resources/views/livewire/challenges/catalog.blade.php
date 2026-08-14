<section class="w-full">
    <flux:heading size="lg">{{ __('Retos disponibles') }}</flux:heading>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($this->challenges as $challenge)
            @php($completion = $challenge->completions->first())

            <flux:card class="space-y-2">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ $challenge->title }}</flux:heading>
                    <flux:badge>{{ $challenge->points }} pts</flux:badge>
                </div>
                <flux:text>{{ $challenge->description }}</flux:text>
                <flux:badge variant="pill">{{ $challenge->category }}</flux:badge>
                <flux:badge variant="pill">{{ $challenge->difficulty }}</flux:badge>

                @if ($completion)
                    <flux:badge :variant="$completion->status === 'verified' ? 'success' : ($completion->status === 'rejected' ? 'danger' : 'primary')">
                        {{ __(ucfirst($completion->status)) }}
                    </flux:badge>
                @else
                    <form wire:submit="complete('{{ $challenge->ulid }}')" class="space-y-2">
                        <flux:input type="file" wire:model="evidence" :label="__('Evidencia (opcional)')" />
                        <flux:button size="sm" variant="primary" type="submit">{{ __('Completar reto') }}</flux:button>
                    </form>
                @endif
            </flux:card>
        @endforeach
    </div>
</section>
