<section class="w-full">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Acudientes') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Directorio de acudientes de todas las instituciones.') }}</flux:text>
        </div>
        @if ($this->selectedInstitution)
            @can('manageActors', $this->selectedInstitution)
                <flux:button icon="heart" variant="primary" href="{{ route('actors.guardians.create', ['institution' => $this->selectedInstitution->uuid]) }}" wire:navigate>{{ __('Nuevo acudiente') }}</flux:button>
            @endcan
        @endif
    </div>

    <div class="mb-4 grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm sm:grid-cols-3 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:select wire:model.live="institutionUuid" :label="__('Institución')">
            <flux:select.option value="">{{ __('Todas las instituciones') }}</flux:select.option>
            @foreach ($this->institutions as $institution)
                <flux:select.option value="{{ $institution->uuid }}">{{ $institution->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="groupId" :label="__('Grupo')" :disabled="! $this->selectedInstitution">
            <flux:select.option value="">{{ __('Todos los grupos') }}</flux:select.option>
            @foreach ($this->groups as $group)
                <flux:select.option value="{{ $group->id }}">{{ $group->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Buscar')" :placeholder="__('Nombre o número de documento...')" />
    </div>

    <div
        wire:key="staff-guardians-{{ $this->guardiansCacheKey }}"
        x-data="{
            selected: null,
            details: @js($this->guardianDetails),
            documentTypeLabels: {
                cedula_ciudadania: '{{ __('Cédula de ciudadanía') }}',
                cedula_extranjeria: '{{ __('Cédula de extranjería') }}',
                pasaporte: '{{ __('Pasaporte') }}',
            },
            show(id) { this.selected = this.details[id]; $dispatch('modal-show', { name: 'guardian-detail' }); },
        }"
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($this->guardians as $guardian)
                <div wire:key="guardian-{{ $guardian->id }}" class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-teal-bg text-sm font-bold text-teal-deep">
                            {{ $guardian->initials() }}
                        </span>
                        <div class="min-w-0">
                            <button type="button" x-on:click="show({{ $guardian->id }})" class="text-left font-semibold text-brand-text hover:text-teal-deep hover:underline">
                                {{ $guardian->name }}
                            </button>
                            <flux:text class="block truncate text-sm text-brand-text-muted!">
                                {{ $guardian->document_number ?? __('Sin documento') }}
                                @if ($guardian->phone)
                                    · {{ $guardian->phone }}
                                @endif
                            </flux:text>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">
                            {{ trans_choice(':count estudiante|:count estudiantes', $guardian->guardianStudents->count(), ['count' => $guardian->guardianStudents->count()]) }}
                        </span>
                        <div class="ml-1 flex items-center gap-1 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                            <flux:button size="sm" variant="ghost" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show({{ $guardian->id }})" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-3 px-6 py-16 text-center">
                    <span class="flex size-14 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                        <flux:icon icon="heart" variant="outline" class="size-7" />
                    </span>
                    <flux:text class="text-brand-text-muted!">{{ __('No se encontraron acudientes con estos filtros.') }}</flux:text>
                </div>
            @endforelse
        </div>

        @if ($this->guardians->hasPages())
            <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                {{ $this->guardians->links() }}
            </div>
        @endif

        <flux:modal name="guardian-detail" :dismissible="false" class="w-full max-w-lg">
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
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Celular') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.phone || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Fecha de nacimiento') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.birth_date || '—'"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dirección') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.address || '—'"></div>
                        </div>
                    </div>

                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Estudiantes asignados') }}</flux:heading>
                        <ul class="space-y-1 text-sm" x-show="selected.students.length">
                            <template x-for="student in selected.students" :key="student.name + student.institution">
                                <li class="flex items-center justify-between text-brand-text">
                                    <span x-text="student.name"></span>
                                    <span class="text-xs font-semibold text-brand-text-muted uppercase" x-text="(student.institution || '') + (student.group ? ' · ' + student.group : '')"></span>
                                </li>
                            </template>
                        </ul>
                        <flux:text x-show="!selected.students.length" class="text-brand-text-muted!">{{ __('Sin estudiantes que coincidan con estos filtros.') }}</flux:text>
                    </div>

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
