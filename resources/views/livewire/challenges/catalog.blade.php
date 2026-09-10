<section class="w-full">
    <flux:heading size="lg">{{ __('challenge_available_title') }}</flux:heading>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->challenges as $challenge)
            @php($completion = $challenge->completions->first())

            <flux:card wire:key="challenge-{{ $challenge->ulid }}" class="space-y-2">
                <div class="flex items-center justify-between">
                    <flux:heading size="sm">{{ $challenge->title }}</flux:heading>
                    <flux:badge>
                        {{ $completion?->status === 'verified' ? $completion->points_earned : $challenge->points }} pts
                    </flux:badge>
                </div>
                <flux:text>{{ $challenge->description }}</flux:text>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:badge size="sm" variant="pill" icon="tag" color="zinc">{{ $challenge->category }}</flux:badge>
                    <flux:badge size="sm" :color="match ($challenge->difficulty) {
                        'easy' => 'green',
                        'medium' => 'amber',
                        'hard' => 'red',
                    }">
                        {{ __(match ($challenge->difficulty) {
                            'easy' => 'challenge_easy',
                            'medium' => 'challenge_medium',
                            'hard' => 'challenge_hard',
                        }) }}
                    </flux:badge>
                    @if ($challenge->ends_at)
                        <flux:badge
                            size="sm"
                            icon="clock"
                            :color="$challenge->ends_at->isPast() ? 'red' : ($challenge->ends_at->diffInDays(now()) <= 3 ? 'amber' : 'zinc')"
                        >
                            {{ __('challenge_ends_at_label') }} {{ $challenge->ends_at->translatedFormat('d M') }}
                        </flux:badge>
                    @endif
                </div>

                @if ($completion)
                    <flux:badge :variant="$completion->status === 'verified' ? 'success' : ($completion->status === 'rejected' ? 'danger' : 'primary')">
                        {{ __(match ($completion->status) {
                            'verified' => 'challenge_completion_verified',
                            'rejected' => 'challenge_completion_rejected',
                            'submitted' => 'challenge_completion_submitted',
                            default => 'challenge_completion_pending',
                        }) }}
                    </flux:badge>
                @elseif ($challenge->questions_count > 0)
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('challenge_has_questions_notice') }}</flux:text>
                @else
                    <form wire:submit="complete('{{ $challenge->ulid }}')" class="space-y-2">
                        <flux:input type="file" wire:model="evidence.{{ $challenge->ulid }}" :label="__('challenge_evidence_optional')" />
                        <flux:error name="evidence.{{ $challenge->ulid }}" />
                        <flux:button
                            size="sm"
                            variant="primary"
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="complete('{{ $challenge->ulid }}')"
                        >
                            {{ __('challenge_complete_button') }}
                        </flux:button>
                    </form>
                @endif
            </flux:card>
        @empty
            <div class="col-span-full flex flex-col items-center gap-3 px-6 py-16 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                    <flux:icon icon="trophy" variant="outline" class="size-7" />
                </span>
                <flux:text class="text-brand-text-muted!">{{ __('challenge_catalog_empty') }}</flux:text>
            </div>
        @endforelse
    </div>
</section>

