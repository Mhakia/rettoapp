<section class="w-full" x-data>
    <div
        class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5 opacity-0 duration-500 ease-out"
        x-init="setTimeout(() => $el.classList.replace('opacity-0', 'opacity-100'), 30)"
    >
        <div class="flex items-center gap-4">
            <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-white/70 text-teal-deep shadow-sm sm:flex dark:bg-white/10">
                <flux:icon icon="heart" variant="micro" class="size-6" />
            </span>
            <div>
                <flux:heading size="xl" class="text-teal-deep!">{{ __('Mis hijos') }}</flux:heading>
                <flux:text class="text-brand-text-muted!">{{ __('Progreso de los estudiantes vinculados a tu cuenta.') }}</flux:text>
            </div>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->children as $index => $child)
            <div
                wire:key="guardian-child-{{ $index }}"
                class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-500 ease-out hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900"
                x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), {{ 60 + $index * 80 }})"
            >
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg" class="text-teal-deep!">{{ $child['name'] }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">{{ $child['institution'] }}</flux:text>
                    </div>
                    <span class="whitespace-nowrap rounded-full bg-teal-bg px-3 py-1 text-xs font-bold tracking-wide text-teal-deep uppercase">
                        {{ $child['group'] }}
                    </span>
                </div>

                <div class="flex gap-2">
                    <div class="flex-1 rounded-lg bg-teal-bg px-3 py-2 text-center">
                        <div class="text-lg font-extrabold text-teal-deep">{{ $child['points'] }}</div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Puntos') }}</div>
                    </div>
                    <div class="flex-1 rounded-lg bg-teal-bg px-3 py-2 text-center">
                        <div class="text-lg font-extrabold text-teal-deep">{{ $child['verifiedCount'] }}</div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Verificados') }}</div>
                    </div>
                    <div class="flex-1 rounded-lg bg-amber-bg px-3 py-2 text-center">
                        <div class="text-lg font-extrabold text-amber">{{ $child['pendingCount'] }}</div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Pendientes') }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <flux:text class="block py-10 text-center text-brand-text-muted!">{{ __('Aún no tienes estudiantes vinculados a tu cuenta.') }}</flux:text>
            </div>
        @endforelse
    </div>
</section>
