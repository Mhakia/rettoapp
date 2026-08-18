@php
    $completions = $this->stats['completions'];
    $totalCompletions = array_sum($completions);
    $donutSegments = [
        ['key' => 'verified', 'label' => __('Verificados'), 'value' => $completions['verified'], 'color' => 'var(--color-teal)'],
        ['key' => 'submitted', 'label' => __('En revisión'), 'value' => $completions['submitted'], 'color' => 'var(--color-amber)'],
        ['key' => 'pending', 'label' => __('Pendientes'), 'value' => $completions['pending'], 'color' => '#9CA3AF'],
        ['key' => 'rejected', 'label' => __('Rechazados'), 'value' => $completions['rejected'], 'color' => '#EF4444'],
    ];
    $radius = 70;
    $circumference = 2 * M_PI * $radius;
    $cumulative = 0;
    foreach ($donutSegments as $i => $segment) {
        $fraction = $totalCompletions > 0 ? $segment['value'] / $totalCompletions : 0;
        $length = $fraction * $circumference;
        $donutSegments[$i]['dasharray'] = round($length, 2).' '.round($circumference - $length, 2);
        $donutSegments[$i]['dashoffset'] = round(-$cumulative, 2);
        $donutSegments[$i]['percent'] = round($fraction * 100);
        $cumulative += $length;
    }

    $trend = $this->enrollmentTrend;
    $maxTrend = max(1, collect($trend)->max('count'));
    $totalNewEnrollments = array_sum(array_column($trend, 'count'));
    $trendPoints = collect($trend)->values()->map(function ($point, $i) use ($trend, $maxTrend) {
        $x = count($trend) > 1 ? ($i / (count($trend) - 1)) * 300 : 150;
        $y = 90 - (($point['count'] / $maxTrend) * 80);

        return [
            'x' => round($x, 1),
            'y' => round($y, 1),
            // Percent-based coordinates for the HTML/CSS dots and tooltips, so they stay
            // perfectly round regardless of how the SVG's non-uniform viewBox gets stretched.
            'xp' => round($x / 300 * 100, 2),
            'yp' => round($y / 100 * 100, 2),
        ];
    });
    $trendPath = $trendPoints->map(fn ($p, $i) => ($i === 0 ? 'M' : 'L').$p['x'].','.$p['y'])->implode(' ');
    $trendAreaPath = $trendPath.' L300,90 L0,90 Z';

    $maxGroupCount = max(1, collect($this->groupBreakdown)->max('count') ?? 1);
    $maxTopStudentPoints = max(1, collect($this->topStudents)->max('points') ?? 1);

    $severityMeta = [
        'high' => ['label' => __('Alta'), 'color' => 'bg-red-500', 'text' => 'text-red-600 dark:text-red-400'],
        'medium' => ['label' => __('Media'), 'color' => 'bg-amber', 'text' => 'text-amber'],
        'low' => ['label' => __('Baja'), 'color' => 'bg-zinc-400', 'text' => 'text-zinc-500'],
    ];
@endphp

<section class="w-full" x-data>
    <div
        class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5 opacity-0 duration-500 ease-out"
        x-init="setTimeout(() => $el.classList.replace('opacity-0', 'opacity-100'), 30)"
    >
        <div class="flex items-center gap-4">
            <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-white/70 text-teal-deep shadow-sm sm:flex dark:bg-white/10">
                <flux:icon icon="building-office" variant="micro" class="size-6" />
            </span>
            <div>
                <flux:heading size="xl" class="text-teal-deep!">{{ $this->institution->name }}</flux:heading>
                <flux:text class="text-brand-text-muted!">{{ __('Resumen general de tu institución.') }}</flux:text>
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['icon' => 'academic-cap', 'label' => __('Estudiantes activos'), 'value' => $this->stats['active_students'], 'color' => 'teal'],
            ['icon' => 'user-group', 'label' => __('Profesores activos'), 'value' => $this->stats['active_teachers'], 'color' => 'teal'],
            ['icon' => 'squares-2x2', 'label' => __('Grupos'), 'value' => $this->stats['groups_count'], 'color' => 'amber'],
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
                        let progress = Math.min((ts - start) / 900, 1);
                        value = Math.floor(progress * target);
                        if (progress < 1) requestAnimationFrame(step); else value = target;
                    }
                    requestAnimationFrame(step);
                "
            >
                <span @class([
                    'absolute inset-y-0 left-0 w-1',
                    'bg-teal' => $card['color'] === 'teal',
                    'bg-amber' => $card['color'] === 'amber',
                    'bg-red-500' => $card['color'] === 'red',
                ])></span>
                <div class="flex items-center gap-3 ps-1.5">
                    <span @class([
                        'flex size-11 shrink-0 items-center justify-center rounded-lg',
                        'bg-teal-bg text-teal-deep' => $card['color'] === 'teal',
                        'bg-amber-bg text-amber' => $card['color'] === 'amber',
                        'bg-red-50 text-red-600 dark:bg-red-950/40 dark:text-red-400' => $card['color'] === 'red',
                    ])>
                        <flux:icon :icon="$card['icon']" variant="micro" class="size-6" />
                    </span>
                    <div>
                        <div class="text-2xl font-extrabold text-brand-text" x-text="value"></div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-5">
        {{-- Challenge completions donut --}}
        <div
            class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out lg:col-span-2 dark:border-zinc-700 dark:bg-zinc-900"
            x-data
            x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 120)"
        >
            <flux:heading size="lg" class="mb-1">{{ __('Retos completados') }}</flux:heading>
            <flux:text class="mb-4 text-sm text-brand-text-muted!">{{ __('Estado de las evidencias enviadas por tus estudiantes.') }}</flux:text>

            <div class="flex flex-col items-center gap-6 sm:flex-row">
                <div
                    class="relative shrink-0"
                    x-data="{ animated: false }"
                    x-init="setTimeout(() => animated = true, 100)"
                >
                    <svg viewBox="0 0 200 200" class="size-40 -rotate-90">
                        <circle cx="100" cy="100" r="{{ $radius }}" fill="none" stroke="currentColor" class="text-zinc-100 dark:text-zinc-800" stroke-width="24" />
                        @foreach ($donutSegments as $segment)
                            <circle
                                cx="100" cy="100" r="{{ $radius }}" fill="none"
                                stroke="{{ $segment['color'] }}"
                                stroke-width="24"
                                stroke-linecap="round"
                                stroke-dashoffset="{{ $segment['dashoffset'] }}"
                                :stroke-dasharray="animated ? '{{ $segment['dasharray'] }}' : '0 {{ round($circumference, 2) }}'"
                                class="transition-[stroke-dasharray] duration-1000 ease-out"
                            />
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-extrabold text-brand-text">{{ $totalCompletions }}</span>
                        <span class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Total') }}</span>
                    </div>
                </div>

                <div class="w-full space-y-2">
                    @foreach ($donutSegments as $segment)
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2">
                                <span class="size-2.5 rounded-full" style="background-color: {{ $segment['color'] }}"></span>
                                {{ $segment['label'] }}
                            </span>
                            <span class="font-semibold text-brand-text">{{ $segment['value'] }} <span class="text-brand-text-muted">({{ $segment['percent'] }}%)</span></span>
                        </div>
                    @endforeach
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-teal-bg px-3 py-2 text-sm">
                        <span class="font-semibold text-teal-deep">{{ __('Puntos ganados') }}</span>
                        <span class="font-extrabold text-teal-deep">{{ number_format($this->stats['total_points']) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Groups bar chart --}}
        <div
            class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out lg:col-span-3 dark:border-zinc-700 dark:bg-zinc-900"
            x-data
            x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 180)"
        >
            <flux:heading size="lg" class="mb-1">{{ __('Estudiantes por grupo') }}</flux:heading>
            <flux:text class="mb-4 text-sm text-brand-text-muted!">{{ __('Los grupos con más estudiantes activos.') }}</flux:text>

            @if (count($this->groupBreakdown))
                <div class="space-y-3" x-data="{ animated: false }" x-init="setTimeout(() => animated = true, 100)">
                    @foreach ($this->groupBreakdown as $group)
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-medium text-brand-text">{{ $group['name'] }}</span>
                                <span class="font-semibold text-brand-text-muted">{{ $group['count'] }}</span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div
                                    class="h-full rounded-full bg-teal transition-all duration-700 ease-out"
                                    :style="animated ? 'width: {{ $maxGroupCount > 0 ? round(($group['count'] / $maxGroupCount) * 100, 1) : 0 }}%' : 'width: 0%'"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text class="py-8 text-center text-brand-text-muted!">{{ __('Aún no hay grupos con estudiantes.') }}</flux:text>
            @endif
        </div>
    </div>

    {{-- Enrollment trend --}}
    <div
        class="mb-6 translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out dark:border-zinc-700 dark:bg-zinc-900"
        x-data
        x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 240)"
    >
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg" class="mb-1">{{ __('Nuevas matrículas') }}</flux:heading>
                <flux:text class="text-sm text-brand-text-muted!">{{ __('Estudiantes y profesores matriculados en los últimos 6 meses.') }}</flux:text>
            </div>
            <span class="flex items-center gap-2 rounded-full bg-teal-bg px-3 py-1.5">
                <span class="size-2 rounded-full bg-teal-deep"></span>
                <span class="text-xs font-bold text-teal-deep uppercase">{{ __(':count en total', ['count' => $totalNewEnrollments]) }}</span>
            </span>
        </div>

        <div class="relative h-48 w-full sm:h-56" x-data="{ shown: false }" x-init="setTimeout(() => shown = true, 320)">
            {{-- Faint horizontal guide lines --}}
            <div class="pointer-events-none absolute inset-0 flex flex-col justify-between">
                @for ($i = 0; $i < 4; $i++)
                    <div class="border-t border-dashed border-zinc-100 dark:border-zinc-800"></div>
                @endfor
            </div>

            <svg viewBox="0 0 300 100" class="absolute inset-0 h-full w-full overflow-visible" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="trendGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="var(--color-teal)" stop-opacity="0.35" />
                        <stop offset="100%" stop-color="var(--color-teal)" stop-opacity="0" />
                    </linearGradient>
                </defs>
                <path
                    d="{{ $trendAreaPath }}"
                    fill="url(#trendGradient)"
                    stroke="none"
                    class="transition-opacity duration-700 ease-out"
                    :class="shown ? 'opacity-100' : 'opacity-0'"
                />
                <path
                    d="{{ $trendPath }}"
                    fill="none"
                    stroke="var(--color-teal-deep)"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    vector-effect="non-scaling-stroke"
                    class="transition-opacity duration-700 ease-out"
                    :class="shown ? 'opacity-100' : 'opacity-0'"
                />
            </svg>

            {{-- Data points live as HTML/CSS dots (not SVG circles) so they stay perfectly round
                 no matter how the chart's non-uniform viewBox gets stretched to fill the width. --}}
            @foreach ($trendPoints as $index => $point)
                <div class="group absolute -translate-x-1/2 -translate-y-1/2" style="left: {{ $point['xp'] }}%; top: {{ $point['yp'] }}%">
                    <div
                        class="size-2.5 scale-0 rounded-full bg-teal-deep ring-4 ring-teal-bg transition-transform duration-500 ease-out group-hover:scale-150 dark:ring-zinc-900"
                        :class="shown && 'scale-100'"
                        style="transition-delay: {{ 650 + $index * 90 }}ms"
                    ></div>
                    <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2.5 -translate-x-1/2 rounded-lg bg-zinc-800 px-2.5 py-1.5 text-xs font-semibold whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100 dark:bg-zinc-950">
                        {{ $trend[$index]['label'] }} · {{ $trend[$index]['count'] }}
                        <div class="absolute top-full left-1/2 size-0 -translate-x-1/2 border-4 border-transparent border-t-zinc-800 dark:border-t-zinc-950"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-2 flex justify-between text-xs font-semibold text-brand-text-muted uppercase">
            @foreach ($trend as $point)
                <span>{{ $point['label'] }}</span>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Recent alerts --}}
        <div
            class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out dark:border-zinc-700 dark:bg-zinc-900"
            x-data
            x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 300)"
        >
            <flux:heading size="lg" class="mb-1">{{ __('Alertas recientes') }}</flux:heading>
            <flux:text class="mb-4 text-sm text-brand-text-muted!">{{ __('Casos abiertos que requieren seguimiento.') }}</flux:text>

            <div class="mb-4 flex gap-2">
                @foreach (['high', 'medium', 'low'] as $severity)
                    <span class="flex-1 rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-800/50">
                        <span class="block text-lg font-extrabold {{ $severityMeta[$severity]['text'] }}">{{ $this->stats['alerts_by_severity'][$severity] }}</span>
                        <span class="text-xs font-semibold text-brand-text-muted uppercase">{{ $severityMeta[$severity]['label'] }}</span>
                    </span>
                @endforeach
            </div>

            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->recentAlerts as $alert)
                    <div class="flex items-center justify-between gap-3 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                        <div class="min-w-0">
                            <div class="truncate font-medium text-brand-text">{{ $alert->student->user->name }}</div>
                            <div class="truncate text-sm text-brand-text-muted">{{ $alert->message }}</div>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-1 text-xs font-bold uppercase',
                            'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400' => $alert->severity === 'high',
                            'bg-amber-bg text-amber' => $alert->severity === 'medium',
                            'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' => $alert->severity === 'low',
                        ])>
                            {{ $severityMeta[$alert->severity]['label'] }}
                        </span>
                    </div>
                @empty
                    <flux:text class="py-8 text-center text-brand-text-muted!">{{ __('No hay alertas abiertas. ¡Todo en orden!') }}</flux:text>
                @endforelse
            </div>
        </div>

        {{-- Top students --}}
        <div
            class="translate-y-2 rounded-xl border border-zinc-200 bg-white p-6 opacity-0 shadow-sm transition-all duration-700 ease-out dark:border-zinc-700 dark:bg-zinc-900"
            x-data
            x-init="setTimeout(() => $el.classList.remove('opacity-0', 'translate-y-2'), 360)"
        >
            <flux:heading size="lg" class="mb-1">{{ __('Estudiantes destacados') }}</flux:heading>
            <flux:text class="mb-4 text-sm text-brand-text-muted!">{{ __('Los que más puntos han ganado con retos verificados.') }}</flux:text>

            @if (count($this->topStudents))
                <div class="space-y-3" x-data="{ animated: false }" x-init="setTimeout(() => animated = true, 100)">
                    @foreach ($this->topStudents as $index => $student)
                        <div class="flex items-center gap-3">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-teal-bg text-xs font-bold text-teal-deep">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="mb-1 flex items-center justify-between text-sm">
                                    <span class="truncate font-medium text-brand-text">{{ $student['name'] }}</span>
                                    <span class="shrink-0 font-semibold text-brand-text-muted">{{ number_format($student['points']) }} pts</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    <div
                                        class="h-full rounded-full bg-amber transition-all duration-700 ease-out"
                                        :style="animated ? 'width: {{ round(($student['points'] / $maxTopStudentPoints) * 100, 1) }}%' : 'width: 0%'"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:text class="py-8 text-center text-brand-text-muted!">{{ __('Aún no hay retos verificados.') }}</flux:text>
            @endif
        </div>
    </div>
</section>
