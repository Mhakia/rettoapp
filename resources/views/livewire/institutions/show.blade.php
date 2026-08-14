<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ $institution->name }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ $institution->nit ?? __('Sin NIT') }} · {{ $institution->address ?? __('Sin dirección') }}</flux:text>
        </div>
        <flux:button icon="arrow-left" href="{{ route('institutions.index') }}" wire:navigate>{{ __('Volver a instituciones') }}</flux:button>
    </div>

    <flux:radio.group wire:model.live="tab" variant="segmented" class="mb-6">
        <flux:radio value="student" :label="__('Estudiantes')" />
        <flux:radio value="teacher" :label="__('Profesores')" />
        <flux:radio value="group" :label="__('Grupos')" />
    </flux:radio.group>

    <div class="rounded-xl border border-zinc-200 bg-white p-2 dark:border-zinc-700 dark:bg-zinc-900">
        @if ($tab === 'group')
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->groups as $group)
                    <div class="flex items-center justify-between px-4 py-3">
                        <flux:text class="font-semibold text-brand-text!">{{ $group->name }}</flux:text>
                        <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">
                            {{ __(':count matrículas', ['count' => $group->memberships_count]) }}
                        </span>
                    </div>
                @empty
                    <flux:text class="px-4 py-6 text-brand-text-muted!">{{ __('No hay grupos registrados.') }}</flux:text>
                @endforelse
            </div>
        @else
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->memberships as $membership)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <flux:text class="font-semibold text-brand-text!">{{ $membership->user->name }}</flux:text>
                            <flux:text class="text-sm text-brand-text-muted!">{{ $membership->group?->name ?? __('Sin grupo') }}</flux:text>
                        </div>
                        <span class="rounded-full bg-teal-bg px-3 py-1 text-xs font-bold text-teal-deep uppercase">
                            {{ __('Desde :date', ['date' => $membership->started_at->format('d/m/Y')]) }}
                        </span>
                    </div>
                @empty
                    <flux:text class="px-4 py-6 text-brand-text-muted!">{{ __('No hay matrículas activas para este rol.') }}</flux:text>
                @endforelse
            </div>
        @endif
    </div>
</section>
