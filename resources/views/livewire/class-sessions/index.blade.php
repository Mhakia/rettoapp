@php
    $viewingGroup = $this->viewingGroupId ? collect($this->groups)->firstWhere('id', $this->viewingGroupId) : null;
    $resultsGroup = $this->resultsGroupId ? collect($this->groups)->firstWhere('id', $this->resultsGroupId) : null;
@endphp

<section class="w-full">
    @if ($viewingGroup)
        <div class="flex min-h-[60vh] flex-col items-center justify-center gap-6 text-center">
            <flux:button variant="ghost" icon="arrow-left" wire:click="backToList" class="self-start">
                {{ __('Volver al listado') }}
            </flux:button>

            <flux:heading size="xl" class="text-teal-deep!">{{ $viewingGroup['name'] }}</flux:heading>

            <div class="rounded-2xl border border-teal-border bg-teal-bg px-10 py-8">
                <div class="text-sm font-semibold text-brand-text-muted uppercase">{{ __('Código de la clase') }}</div>

                <div class="mt-2 flex items-center justify-center gap-4">
                    <div class="text-8xl font-extrabold tracking-[0.2em] text-teal-deep">{{ $viewingGroup['session']['code'] }}</div>

                    <button
                        type="button"
                        x-data="{ copied: false }"
                        x-on:click="navigator.clipboard.writeText('{{ $viewingGroup['session']['code'] }}'); copied = true; setTimeout(() => copied = false, 1500)"
                        class="flex size-10 shrink-0 items-center justify-center rounded-lg text-teal-deep hover:bg-white/60 dark:hover:bg-white/10"
                        :title="__('Copiar código')"
                    >
                        <flux:icon x-show="!copied" icon="clipboard" variant="micro" class="size-5" />
                        <flux:icon x-show="copied" x-cloak icon="check" variant="micro" class="size-5 text-teal-deep" />
                    </button>
                </div>

                <div class="mt-3 text-sm text-brand-text-muted">{{ __('Vence: :date', ['date' => $viewingGroup['session']['expires_at']]) }}</div>
            </div>

            <flux:modal.trigger name="confirm-close-session">
                <flux:button variant="danger" wire:click="$set('confirmingSessionId', {{ $viewingGroup['session']['id'] }})">
                    {{ __('Cerrar sesión') }}
                </flux:button>
            </flux:modal.trigger>
        </div>
    @elseif ($resultsGroup)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <div class="flex items-center gap-4">
                <flux:button variant="ghost" icon="arrow-left" wire:click="backToListFromResults">
                    {{ __('Volver') }}
                </flux:button>
                <div>
                    <flux:heading size="xl" class="text-teal-deep!">{{ $resultsGroup['name'] }}</flux:heading>
                    <flux:text class="text-brand-text-muted!">{{ __('Resultados de retos de este grupo.') }}</flux:text>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            @forelse ($this->resultsFor($resultsGroup['id']) as $result)
                <div wire:key="result-{{ $result['ulid'] }}" class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <button
                        type="button"
                        wire:click="toggleChallengeDetail('{{ $result['ulid'] }}')"
                        class="flex w-full flex-wrap items-center justify-between gap-3 p-5 text-left"
                    >
                        <div>
                            <flux:heading size="md" class="text-teal-deep!">{{ $result['title'] }}</flux:heading>
                            <flux:text class="text-xs text-brand-text-muted!">{{ $result['code'] }} · {{ trans_choice(':count estudiante|:count estudiantes', $result['total_students'], ['count' => $result['total_students']]) }}</flux:text>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-teal-bg px-2.5 py-1 text-teal-deep">{{ $result['verified_count'] }} {{ __('completados') }}</span>
                            <span class="rounded-full bg-amber-bg px-2.5 py-1 text-amber">{{ $result['submitted_count'] }} {{ __('por revisar') }}</span>
                            <span class="rounded-full bg-red-100 px-2.5 py-1 text-red-600 dark:bg-red-500/10 dark:text-red-400">{{ $result['rejected_count'] }} {{ __('rechazados') }}</span>
                            <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-zinc-600 dark:bg-white/10 dark:text-zinc-300">{{ $result['not_started_count'] }} {{ __('sin responder') }}</span>
                            <flux:icon icon="{{ $this->resultsChallengeUlid === $result['ulid'] ? 'chevron-up' : 'chevron-down' }}" variant="micro" class="size-4 text-brand-text-muted" />
                        </div>
                    </button>

                    @if ($this->resultsChallengeUlid === $result['ulid'])
                        <div class="divide-y divide-zinc-100 border-t border-zinc-100 dark:divide-white/5 dark:border-white/5">
                            @foreach ($result['rows'] as $row)
                                <div wire:key="result-{{ $result['ulid'] }}-row-{{ $loop->index }}" class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm">
                                    <span class="font-medium">{{ $row['name'] }}</span>

                                    <div class="flex items-center gap-3">
                                        <span @class([
                                            'rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-teal-bg text-teal-deep' => $row['status'] === 'verified',
                                            'bg-amber-bg text-amber' => $row['status'] === 'submitted',
                                            'bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400' => $row['status'] === 'rejected',
                                            'bg-zinc-100 text-zinc-600 dark:bg-white/10 dark:text-zinc-300' => in_array($row['status'], ['pending', 'not_started']),
                                        ])>
                                            {{ match ($row['status']) {
                                                'verified' => __('Verificado'),
                                                'submitted' => __('Por revisar'),
                                                'rejected' => __('Rechazado'),
                                                default => __('Sin responder'),
                                            } }}
                                        </span>

                                        <span class="text-xs text-brand-text-muted">
                                            {{ match ($row['origin']) {
                                                'class_session' => __('En clase'),
                                                'guardian' => __('En casa'),
                                                default => '—',
                                            } }}
                                        </span>

                                        <span class="w-16 text-right text-xs text-brand-text-muted">{{ $row['duration'] ?? '—' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <flux:text class="block py-10 text-center text-brand-text-muted!">{{ __('No hay retos publicados para estudiantes todavía.') }}</flux:text>
            @endforelse
        </div>
    @else
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <div class="flex items-center gap-4">
                <span class="hidden size-12 shrink-0 items-center justify-center rounded-xl bg-white/70 text-teal-deep shadow-sm sm:flex dark:bg-white/10">
                    <flux:icon icon="qr-code" variant="micro" class="size-6" />
                </span>
                <div>
                    <flux:heading size="xl" class="text-teal-deep!">{{ __('Sesiones de retos') }}</flux:heading>
                    <flux:text class="text-brand-text-muted!">{{ __('Genera un código para que tus estudiantes entren a sus retos sin usuario ni contraseña.') }}</flux:text>
                </div>
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->groups as $index => $group)
                <div
                    wire:key="class-session-group-{{ $group['id'] }}"
                    class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div class="mb-3">
                        <flux:heading size="lg" class="text-teal-deep!">{{ $group['name'] }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ trans_choice(':count estudiante|:count estudiantes', $group['students'], ['count' => $group['students']]) }}
                        </flux:text>
                    </div>

                    @if ($group['session'])
                        <div class="rounded-lg bg-teal-bg px-4 py-3 text-center">
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Código activo') }}</div>
                            <div class="text-3xl font-extrabold tracking-widest text-teal-deep">{{ $group['session']['code'] }}</div>
                            <div class="mt-1 text-xs text-brand-text-muted">{{ __('Vence: :date', ['date' => $group['session']['expires_at']]) }}</div>
                        </div>

                        <div class="mt-3 flex gap-2">
                            <flux:button icon="eye" variant="ghost" class="flex-1" :tooltip="__('Ver código en grande')" wire:click="viewCode({{ $group['id'] }})" />

                            <flux:modal.trigger name="confirm-close-session">
                                <flux:button variant="danger" class="flex-1" wire:click="$set('confirmingSessionId', {{ $group['session']['id'] }})">
                                    {{ __('Cerrar sesión') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </div>
                    @elseif ($group['blocked'])
                        <div class="flex items-start gap-2 rounded-lg bg-amber-bg px-3 py-2.5">
                            <flux:icon icon="lock-closed" variant="micro" class="mt-0.5 size-4 shrink-0 text-amber" />
                            <flux:text class="text-sm text-amber!">
                                {{ __('Tienes una sesión activa en otro grupo. Ciérrala para poder iniciar esta.') }}
                            </flux:text>
                        </div>
                    @elseif ($this->pickingGroupId === $group['id'])
                        <div class="space-y-3">
                            <flux:select wire:model="challengeId" :label="__('Reto')" :placeholder="__('Elige un reto')">
                                @foreach ($this->challengeOptions as $option)
                                    <flux:select.option value="{{ $option['id'] }}">{{ $option['title'] }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:radio.group wire:model="duration">
                                <flux:radio value="2h" label="{{ __('2 horas') }}" />
                                <flux:radio value="today" label="{{ __('Hasta el fin del día') }}" />
                                <flux:radio value="3d" label="{{ __('3 días') }}" />
                            </flux:radio.group>

                            <div class="flex gap-2">
                                <flux:button variant="ghost" class="flex-1" wire:click="cancelPicking">
                                    {{ __('Cancelar') }}
                                </flux:button>
                                <flux:button variant="primary" class="flex-1 bg-teal! hover:bg-teal-deep!" wire:click="startSession({{ $group['id'] }})">
                                    {{ __('Generar código') }}
                                </flux:button>
                            </div>
                        </div>
                    @else
                        <flux:button variant="primary" class="w-full bg-teal! hover:bg-teal-deep!" wire:click="startPicking({{ $group['id'] }})">
                            {{ __('Iniciar sesión de retos') }}
                        </flux:button>
                    @endif

                    <flux:button variant="ghost" icon="chart-bar" class="mt-2 w-full" wire:click="viewResults({{ $group['id'] }})">
                        {{ __('Ver resultados') }}
                    </flux:button>
                </div>
            @empty
                <div class="col-span-full">
                    <flux:text class="block py-10 text-center text-brand-text-muted!">{{ __('No tienes grupos asignados todavía.') }}</flux:text>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Single shared modal, always present regardless of which card triggers it. --}}
    <flux:modal name="confirm-close-session" :dismissible="false" class="max-w-sm">
        <div class="space-y-4 text-center">
            <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                <flux:icon icon="exclamation-triangle" variant="micro" class="size-6" />
            </span>

            <div>
                <flux:heading size="lg">{{ __('¿Cerrar esta sesión de retos?') }}</flux:heading>
                <flux:text class="mt-1 text-brand-text-muted!">
                    {{ __('Los estudiantes ya no podrán entrar con este código. Los retos que ya respondieron no se ven afectados.') }}
                </flux:text>
            </div>

            <div class="flex justify-center gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="cancelClose">{{ __('Cancelar') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="confirmClose">{{ __('Sí, cerrar sesión') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</section>

