<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ $institution->name }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ $institution->nit ?? __('Sin NIT') }} · {{ $institution->address ?? __('Sin dirección') }}</flux:text>
        </div>
        <flux:button icon="arrow-left" href="{{ route('institutions.index') }}" wire:navigate>{{ __('Volver a instituciones') }}</flux:button>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <flux:radio.group wire:model.live="tab" variant="segmented">
            <flux:radio value="student" :label="__('Estudiantes')" />
            <flux:radio value="teacher" :label="__('Profesores')" />
            <flux:radio value="group" :label="__('Grupos')" />
        </flux:radio.group>

        @if ($tab === 'student')
            @can('manageActors', $institution)
                <div class="flex flex-wrap gap-2">
                    <flux:button icon="arrow-up-tray" href="{{ route('actors.students.import', ['institution' => $institution->uuid]) }}" wire:navigate>
                        {{ __('Carga masiva') }}
                    </flux:button>
                    <flux:button icon="user-plus" variant="primary" href="{{ route('actors.students.create', ['institution' => $institution->uuid]) }}" wire:navigate>
                        {{ __('Nuevo estudiante') }}
                    </flux:button>
                </div>
            @endcan
        @elseif ($tab === 'teacher')
            @can('manageActors', $institution)
                <div class="flex flex-wrap gap-2">
                    <flux:button icon="arrow-up-tray" href="{{ route('actors.teachers.import', ['institution' => $institution->uuid]) }}" wire:navigate>
                        {{ __('Carga masiva') }}
                    </flux:button>
                    <flux:button icon="user-plus" variant="primary" href="{{ route('actors.teachers.create', ['institution' => $institution->uuid]) }}" wire:navigate>
                        {{ __('Nuevo profesor') }}
                    </flux:button>
                </div>
            @endcan
        @endif
    </div>

    @if ($tab !== 'group')
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o número de documento...')" class="mb-6 max-w-md" />
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white p-2 dark:border-zinc-700 dark:bg-zinc-900">
        @if ($tab === 'group')
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->groups as $group)
                    <div wire:key="group-{{ $group->id }}" class="flex items-center justify-between px-4 py-3">
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
                    documentTypeLabels: {
                        registro_civil: '{{ __('Registro civil') }}',
                        tarjeta_identidad: '{{ __('Tarjeta de identidad') }}',
                        cedula_ciudadania: '{{ __('Cédula de ciudadanía') }}',
                        cedula_extranjeria: '{{ __('Cédula de extranjería') }}',
                        pasaporte: '{{ __('Pasaporte') }}',
                    },
                    show(id) { this.selected = this.details[id]; $dispatch('modal-show', { name: 'membership-detail' }); },
                }"
            >
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->memberships as $membership)
                    <div wire:key="membership-{{ $membership->id }}" class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-teal-bg text-sm font-bold text-teal-deep">
                                {{ $membership->user->initials() }}
                            </span>
                            <div class="min-w-0">
                                <button type="button" x-on:click="show({{ $membership->id }})" class="text-left font-semibold text-brand-text! hover:underline">
                                    {{ $membership->user->name }}
                                </button>
                                <flux:text class="block truncate text-sm text-brand-text-muted!">
                                    @if ($tab === 'student')
                                        {{ $membership->user->studentProfile?->document_number ?? __('Sin documento') }}
                                    @else
                                        {{ $membership->user->document_number ?? __('Sin documento') }}
                                        @if ($membership->user->phone)
                                            · {{ $membership->user->phone }}
                                        @endif
                                    @endif
                                </flux:text>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            @if ($tab === 'student')
                                <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">
                                    {{ $membership->group?->name ?? __('Sin grupo') }}
                                </span>
                            @else
                                @forelse ($membership->user->teacherGroups as $group)
                                    <span wire:key="membership-{{ $membership->id }}-group-{{ $group->id }}" class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">{{ $group->name }}</span>
                                @empty
                                    <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-500 uppercase dark:bg-zinc-800">{{ __('Sin grupos') }}</span>
                                @endforelse
                            @endif
                            <span class="whitespace-nowrap rounded-full bg-teal-bg px-3 py-1 text-xs font-bold text-teal-deep uppercase">
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
                        <div class="flex items-center gap-3">
                            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-teal-bg text-base font-bold text-teal-deep" x-text="selected.initials"></span>
                            <div>
                                <flux:heading size="lg" x-text="selected.name"></flux:heading>
                                <flux:text class="text-sm text-brand-text-muted!" x-text="selected.email"></flux:text>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-800/50">
                            <div>
                                <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Documento') }}</div>
                                <div class="font-medium text-brand-text" x-text="(documentTypeLabels[selected.document_type] || selected.document_type || '—') + (selected.document_number ? ' · ' + selected.document_number : '')"></div>
                            </div>
                            <template x-if="'phone' in selected">
                                <div>
                                    <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Celular') }}</div>
                                    <div class="font-medium text-brand-text" x-text="selected.phone || '—'"></div>
                                </div>
                            </template>
                            <div x-show="!('groups' in selected)">
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
                                <div class="flex flex-wrap gap-1" x-show="selected.groups.length">
                                    <template x-for="groupName in selected.groups" :key="groupName">
                                        <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase" x-text="groupName"></span>
                                    </template>
                                </div>
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
