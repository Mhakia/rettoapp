<section class="w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Estudiantes y profesores') }}</flux:heading>
            <flux:text>{{ __('Matrículas activas en tu institución.') }}</flux:text>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button icon="user-plus" href="{{ route('actors.teachers.create') }}" wire:navigate>{{ __('Nuevo profesor') }}</flux:button>
            <flux:button icon="arrow-up-tray" href="{{ route('actors.teachers.import') }}" wire:navigate>{{ __('Cargar profesores') }}</flux:button>
            <flux:button icon="academic-cap" href="{{ route('actors.students.create') }}" wire:navigate>{{ __('Nuevo estudiante') }}</flux:button>
            <flux:button icon="arrow-up-tray" href="{{ route('actors.students.import') }}" wire:navigate>{{ __('Cargar estudiantes') }}</flux:button>
            <flux:button href="{{ route('actors.enroll') }}" wire:navigate>{{ __('Matricular / vincular') }}</flux:button>
        </div>
    </div>

    <flux:radio.group wire:model.live="role" variant="segmented" class="mb-4">
        <flux:radio value="student" :label="__('Estudiantes')" />
        <flux:radio value="teacher" :label="__('Profesores')" />
    </flux:radio.group>

    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o número de documento...')" class="mb-6 max-w-md" />

    <div
        wire:key="roster-{{ $this->membershipsCacheKey }}"
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
    <div class="divide-y">
        @forelse ($this->memberships as $membership)
            <div class="py-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-teal-bg text-sm font-bold text-teal-deep">
                            {{ $membership->user->initials() }}
                        </span>
                        <div class="min-w-0">
                            <button type="button" x-on:click="show({{ $membership->id }})" class="text-left font-medium hover:underline">
                                {{ $membership->user->name }}
                            </button>
                            <flux:text class="block truncate text-sm text-gray-500">
                                @if ($role === 'student')
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
                        @if ($role === 'student')
                            <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">
                                {{ $membership->group?->name ?? __('Sin grupo') }}
                            </span>
                        @else
                            @forelse ($membership->user->teacherGroups as $group)
                                <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">{{ $group->name }}</span>
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
                <livewire:actors.withdraw-membership :membership="$membership" :key="$membership->id" />
            </div>
        @empty
            <flux:text class="py-4">{{ __('No hay matrículas activas para este rol.') }}</flux:text>
        @endforelse
    </div>

    @if ($this->memberships->hasPages())
        <div class="mt-4">
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
</section>
