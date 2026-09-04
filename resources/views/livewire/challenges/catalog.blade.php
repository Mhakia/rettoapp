<section class="w-full">
    <flux:heading size="lg">{{ __('challenge_available_title') }}</flux:heading>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($this->challenges as $challenge)
            @php($completion = $challenge->completions->first())

            <flux:card wire:key="challenge-{{ $challenge->ulid }}" class="space-y-2">
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
                        <flux:input type="file" wire:model="evidence" :label="__('challenge_evidence_optional')" />
                        <flux:button size="sm" variant="primary" type="submit">{{ __('challenge_complete_button') }}</flux:button>
                    </form>
                @endif
            </flux:card>
        @endforeach
    </div>
</section>
