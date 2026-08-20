@php
    $maxTopInstitution = max(1, collect($this->topInstitutions)->max('count') ?? 1);
@endphp

<section class="w-full" x-data>
    <div
        class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5 opacity-0 duration-500 ease-out"
        x-init="setTimeout(() => $el.classList.replace('opacity-0', 'opacity-100'), 30)"
    >
        <div class="flex items-center gap-4">
            <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-white/70 text-teal-deep shadow-sm sm:flex dark:bg-white/10">
                <flux:icon icon="globe-alt" variant="micro" class="size-6" />
            </span>
            <div>
                <flux:heading size="xl" class="text-teal-deep!">{{ __('Panorama de la plataforma') }}</flux:heading>
                <flux:text class="text-brand-text-muted!">{{ __('Resumen agregado de todas las instituciones.') }}</flux:text>
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['icon' => 'building-office', 'label' => __('Instituciones'), 'value' => $this->stats['institutions_count'], 'color' => 'teal'],
            ['icon' => 'academic-cap', 'label' => __('Estudiantes activos'), 'value' => $this->stats['active_students'], 'color' => 'teal'],
            ['icon' => 'user-group', 'label' => __('Profesores activos'), 'value' => $this->stats['active_teachers'], 'color' => 'teal'],
            ['icon' => 'sparkles', 'label' => __('Retos publicados'), 'value' => $this->stats['published_challenges'], 'color' => 'amber'],
            ['icon' => 'exclamation-triangle', 'label' => __('Alertas abiertas'), 'value' => $this->stats['open_alerts'], 'color' => 'red'],
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
                <div class="absolute inset-y-0 left-0 w-1 bg-{{ $card['color'] === 'red' ? 'red-500' : $card['color'] }}"></div>

                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg {{ $card['color'] === 'red' ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300' : ($card['color'] === 'amber' ? 'bg-amber-bg text-amber' : 'bg-teal-bg text-teal-deep') }}">
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

    {{-- Top instituciones por estudiantes activos --}}
    <div
        class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out dark:border-zinc-700 dark:bg-zinc-900"
        x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 420)"
    >
        <flux:heading size="lg" class="mb-4">{{ __('Instituciones con más estudiantes activos') }}</flux:heading>

        @forelse ($this->topInstitutions as $institution)
            <div wire:key="top-institution-{{ $loop->index }}" class="mb-3 last:mb-0">
                <div class="mb-1 flex items-center justify-between text-sm">
                    <span class="font-medium text-brand-text">{{ $institution['name'] }}</span>
                    <span class="font-semibold text-teal-deep">{{ $institution['count'] }}</span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                    <div class="h-full rounded-full bg-teal" style="width: {{ $institution['count'] / $maxTopInstitution * 100 }}%"></div>
                </div>
            </div>
        @empty
            <flux:text class="text-brand-text-muted!">{{ __('Aún no hay instituciones registradas.') }}</flux:text>
        @endforelse
    </div>
</section>
