@php
    $maxGroupStudents = max(1, collect($this->groupBreakdown)->max('students') ?? 1);
@endphp

<section class="w-full" x-data>
    <div
        class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5 opacity-0 duration-500 ease-out"
        x-init="setTimeout(() => $el.classList.replace('opacity-0', 'opacity-100'), 30)"
    >
        <div class="flex items-center gap-4">
            <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-white/70 text-teal-deep shadow-sm sm:flex dark:bg-white/10">
                <flux:icon icon="user-group" variant="micro" class="size-6" />
            </span>
            <div>
                <flux:heading size="xl" class="text-teal-deep!">{{ __('Mis grupos') }}</flux:heading>
                <flux:text class="text-brand-text-muted!">{{ __('Resumen de tus grupos y retos por verificar.') }}</flux:text>
            </div>
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" icon="qr-code" href="{{ route('class-sessions.index') }}" wire:navigate>
                {{ __('Sesiones de retos') }}
            </flux:button>

            <flux:button variant="primary" icon="check-badge" class="bg-teal! hover:bg-teal-deep!" href="{{ route('challenges.verify') }}" wire:navigate>
                {{ __('Verificar retos') }}
            </flux:button>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['icon' => 'squares-2x2', 'label' => __('Grupos a cargo'), 'value' => $this->stats['groups_count'], 'color' => 'teal'],
            ['icon' => 'academic-cap', 'label' => __('Estudiantes'), 'value' => $this->stats['active_students'], 'color' => 'teal'],
            ['icon' => 'clock', 'label' => __('Pendientes por verificar'), 'value' => $this->stats['pending_to_verify'], 'color' => 'amber'],
            ['icon' => 'check-badge', 'label' => __('Retos verificados'), 'value' => $this->stats['verified_count'], 'color' => 'teal'],
        ] as $index => $card)
            <div
                class="relative translate-y-2 overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 opacity-0 shadow-sm transition-all duration-500 ease-out hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900"
                x-data="{ value: 0 }"
                x-init="
                    setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), {{ 60 + $index * 80 }});
                    let target = {{ $card['value'] }}, start = null;
                    function step(ts) {
                        if (!start) start = ts;
                        let progress = Math.min((ts - start) / 600, 1);
                        value = Math.floor(progress * target);
                        if (progress < 1) requestAnimationFrame(step);
                        else value = target;
                    }
                    requestAnimationFrame(step);
                "
            >
                <div class="absolute inset-y-0 left-0 w-1 bg-{{ $card['color'] === 'amber' ? 'amber' : 'teal' }}"></div>

                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $card['color'] === 'amber' ? 'bg-amber-bg text-amber' : 'bg-teal-bg text-teal-deep' }}">
                        <flux:icon icon="{{ $card['icon'] }}" variant="micro" class="size-5" />
                    </span>
                    <div>
                        <div class="text-2xl font-extrabold text-brand-text" x-text="value"></div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Grupos --}}
        <div
            class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out dark:border-zinc-700 dark:bg-zinc-900"
            x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 420)"
        >
            <flux:heading size="lg" class="mb-4">{{ __('Progreso por grupo') }}</flux:heading>

            @forelse ($this->groupBreakdown as $group)
                <div wire:key="teacher-group-{{ $loop->index }}" class="mb-4 last:mb-0">
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-brand-text">{{ $group['name'] }}</span>
                        <span class="text-brand-text-muted">{{ trans_choice(':count estudiante|:count estudiantes', $group['students'], ['count' => $group['students']]) }}</span>
                    </div>
                    <div class="flex h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full bg-teal" style="width: {{ $group['students'] > 0 ? $group['students'] / $maxGroupStudents * 100 : 0 }}%"></div>
                    </div>
                    <div class="mt-1 flex gap-3 text-xs text-brand-text-muted">
                        <span>{{ __(':count verificados', ['count' => $group['verified']]) }}</span>
                        <span>{{ __(':count pendientes', ['count' => $group['pending']]) }}</span>
                    </div>
                </div>
            @empty
                <flux:text class="text-brand-text-muted!">{{ __('Aún no tienes grupos asignados.') }}</flux:text>
            @endforelse
        </div>

        {{-- Pendientes recientes --}}
        <div
            class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out dark:border-zinc-700 dark:bg-zinc-900"
            x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 480)"
        >
            <flux:heading size="lg" class="mb-4">{{ __('Pendientes recientes') }}</flux:heading>

            @forelse ($this->recentPending as $completion)
                <div wire:key="pending-{{ $completion->id }}" class="mb-3 flex items-center justify-between border-b border-zinc-100 pb-3 text-sm last:mb-0 last:border-0 last:pb-0 dark:border-zinc-800">
                    <div>
                        <div class="font-medium text-brand-text">{{ $completion->user->name }}</div>
                        <div class="text-brand-text-muted">{{ $completion->challenge->title }}</div>
                    </div>
                    <span class="whitespace-nowrap rounded-full bg-amber-bg px-3 py-1 text-xs font-bold tracking-wide text-amber uppercase">{{ __('Pendiente') }}</span>
                </div>
            @empty
                <flux:text class="text-brand-text-muted!">{{ __('No hay retos pendientes por verificar.') }}</flux:text>
            @endforelse
        </div>
    </div>
</section>
