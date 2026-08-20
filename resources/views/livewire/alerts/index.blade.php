<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Alertas') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Situaciones reportadas sobre estudiantes.') }}</flux:text>
        </div>

        @can('create', \App\Models\Alert::class)
            <flux:button variant="primary" icon="plus" class="bg-teal! hover:bg-teal-deep!" href="{{ route('alerts.create') }}" wire:navigate>{{ __('Nueva alerta') }}</flux:button>
        @endcan
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <flux:select wire:model.live="status" class="max-w-xs">
            <flux:select.option value="">{{ __('Todos los estados') }}</flux:select.option>
            <flux:select.option value="open">{{ __('Abiertas') }}</flux:select.option>
            <flux:select.option value="resolved">{{ __('Resueltas') }}</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="severity" class="max-w-xs">
            <flux:select.option value="">{{ __('Todas las severidades') }}</flux:select.option>
            <flux:select.option value="low">{{ __('Baja') }}</flux:select.option>
            <flux:select.option value="medium">{{ __('Media') }}</flux:select.option>
            <flux:select.option value="high">{{ __('Alta') }}</flux:select.option>
        </flux:select>
    </div>

    <div
        wire:key="alerts-list-{{ $this->alertsCacheKey }}"
        x-data="{
            selected: null,
            details: @js($this->alertDetails),
            severityLabels: @js(['low' => __('Baja'), 'medium' => __('Media'), 'high' => __('Alta')]),
            show(id) { this.selected = this.details[id]; $dispatch('modal-show', { name: 'alert-detail' }); },
        }"
    >
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->alerts as $alert)
                    <div wire:key="alert-{{ $alert->id }}" class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <div>
                            <button type="button" x-on:click="show({{ $alert->id }})" class="text-left">
                                <flux:heading size="sm" class="hover:underline">{{ $alert->student->user->name ?? __('Estudiante') }}</flux:heading>
                            </button>
                            <flux:text class="text-sm text-brand-text-muted!">{{ $alert->type }} · {{ $alert->created_at->format('d/m/Y') }}</flux:text>
                        </div>

                        <div class="flex items-center gap-2">
                            <span @class([
                                'whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold tracking-wide uppercase',
                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => $alert->severity === 'high',
                                'bg-amber-bg text-amber' => $alert->severity === 'medium',
                                'bg-teal-bg text-teal-deep' => $alert->severity === 'low',
                            ])>
                                {{ ['low' => __('Baja'), 'medium' => __('Media'), 'high' => __('Alta')][$alert->severity] }}
                            </span>

                            <span class="whitespace-nowrap rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold tracking-wide text-zinc-600 uppercase dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $alert->status === 'open' ? __('Abierta') : __('Resuelta') }}
                            </span>

                            <flux:button size="sm" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show({{ $alert->id }})" />

                            @can('resolve', $alert)
                                @if ($alert->status === 'open')
                                    <flux:button size="sm" variant="primary" icon="check" :tooltip="__('Resolver')" wire:click="resolve({{ $alert->id }})" wire:confirm="{{ __('¿Marcar esta alerta como resuelta?') }}" />
                                @endif
                            @endcan
                        </div>
                    </div>
                @empty
                    <flux:text class="block px-6 py-10 text-center text-brand-text-muted!">{{ __('No se encontraron alertas con esos criterios.') }}</flux:text>
                @endforelse
            </div>
        </div>

        @if ($this->alerts->hasPages())
            <div class="mt-6">
                {{ $this->alerts->links() }}
            </div>
        @endif

        <flux:modal name="alert-detail" :dismissible="false" class="w-full max-w-lg">
            <template x-if="selected">
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg" x-text="selected.student"></flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!" x-text="selected.type"></flux:text>
                    </div>

                    <div class="rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-800/50">
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Descripción') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.message"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-800/50">
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Severidad') }}</div>
                            <div class="font-medium text-brand-text" x-text="severityLabels[selected.severity]"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Estado') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.status === 'open' ? '{{ __('Abierta') }}' : '{{ __('Resuelta') }}'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Creada por') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.creator"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Fecha') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.createdAt"></div>
                        </div>
                        <template x-if="selected.resolvedAt">
                            <div class="col-span-2">
                                <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Resuelta el') }}</div>
                                <div class="font-medium text-brand-text" x-text="selected.resolvedAt"></div>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end">
                        <flux:button variant="ghost" x-on:click="$dispatch('modal-close', { name: 'alert-detail' })">{{ __('Cerrar') }}</flux:button>
                    </div>
                </div>
            </template>
        </flux:modal>
    </div>
</section>
