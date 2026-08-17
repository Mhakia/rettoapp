<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ $institution->name }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ $institution->nit ?? __('Sin NIT') }} · {{ $institution->address ?? __('Sin dirección') }}</flux:text>
        </div>
        <flux:button icon="arrow-left" href="{{ route('institutions.index') }}" wire:navigate>{{ __('Volver a instituciones') }}</flux:button>
    </div>

    <flux:radio.group wire:model.live="tab" variant="segmented" class="mb-4">
        <flux:radio value="student" :label="__('Estudiantes')" />
        <flux:radio value="teacher" :label="__('Profesores')" />
        <flux:radio value="group" :label="__('Grupos')" />
    </flux:radio.group>

    @if ($tab !== 'group')
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o número de documento...')" class="mb-6 max-w-md" />
    @endif

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
            <div
                wire:key="show-members-{{ $this->membershipsCacheKey }}"
                x-data="{
                    selected: null,
                    details: @js($this->membershipDetails),
                    show(id) { this.selected = this.details[id]; $dispatch('modal-show', { name: 'membership-detail' }); },
                }"
            >
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->memberships as $membership)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <button type="button" x-on:click="show({{ $membership->id }})" class="text-left font-semibold text-brand-text! hover:underline">
                                {{ $membership->user->name }}
                            </button>
                            <flux:text class="text-sm text-brand-text-muted!">{{ $membership->group?->name ?? __('Sin grupo') }}</flux:text>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-teal-bg px-3 py-1 text-xs font-bold text-teal-deep uppercase">
                                {{ __('Desde :date', ['date' => $membership->started_at->format('d/m/Y')]) }}
                            </span>
                            <flux:button size="sm" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show({{ $membership->id }})" />
                        </div>
                    </div>
                @empty
                    <flux:text class="px-4 py-6 text-brand-text-muted!">{{ __('No hay matrículas activas para este rol.') }}</flux:text>
                @endforelse
            </div>

            @if ($this->memberships->hasPages())
                <div class="p-4">
                    {{ $this->memberships->links() }}
                </div>
            @endif

            <flux:modal name="membership-detail" class="w-full max-w-lg">
                <template x-if="selected">
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="lg" x-text="selected.name"></flux:heading>
                            <flux:text class="text-sm text-brand-text-muted!" x-text="selected.email"></flux:text>
                        </div>

                        <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-800/50">
                            <div>
                                <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Documento') }}</div>
                                <div class="font-medium text-brand-text" x-text="(selected.document_type || '') + ' ' + (selected.document_number || '—')"></div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Grupo') }}</div>
                                <div class="font-medium text-brand-text" x-text="selected.group || '{{ __('Sin grupo') }}'"></div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Matriculado desde') }}</div>
                                <div class="font-medium text-brand-text" x-text="selected.started_at"></div>
                            </div>
                            <template x-if="'birth_date' in selected">
                                <div>
                                    <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Fecha de nacimiento') }}</div>
                                    <div class="font-medium text-brand-text" x-text="selected.birth_date || '—'"></div>
                                </div>
                            </template>
                        </div>

                        <template x-if="'guardians' in selected">
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Acudientes') }}</flux:heading>
                                <ul class="space-y-1 text-sm" x-show="selected.guardians.length">
                                    <template x-for="guardian in selected.guardians" :key="guardian.email">
                                        <li class="text-brand-text" x-text="guardian.name + ' (' + guardian.email + ')'"></li>
                                    </template>
                                </ul>
                                <flux:text x-show="!selected.guardians.length" class="text-brand-text-muted!">{{ __('Sin acudientes vinculados.') }}</flux:text>
                            </div>
                        </template>

                        <template x-if="'groups' in selected">
                            <div>
                                <flux:heading size="sm" class="mb-2">{{ __('Grupos a cargo') }}</flux:heading>
                                <flux:text x-show="selected.groups.length" x-text="selected.groups.join(', ')"></flux:text>
                                <flux:text x-show="!selected.groups.length" class="text-brand-text-muted!">{{ __('Sin grupos asignados.') }}</flux:text>
                            </div>
                        </template>

                        <div class="flex justify-end">
                            <flux:modal.close>
                                <flux:button>{{ __('Cerrar') }}</flux:button>
                            </flux:modal.close>
                        </div>
                    </div>
                </template>
            </flux:modal>
            </div>
        @endif
    </div>
</section>
