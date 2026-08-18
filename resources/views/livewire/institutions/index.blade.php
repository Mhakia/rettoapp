<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Instituciones') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Listado de instituciones registradas en la plataforma.') }}</flux:text>
        </div>

        @can('create', \App\Models\Institution::class)
            <flux:button variant="primary" icon="plus" class="bg-teal! hover:bg-teal-deep!" href="{{ route('institutions.create') }}" wire:navigate>{{ __('Crear institución') }}</flux:button>
        @endcan
    </div>

    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o NIT...')" class="mb-6 max-w-md" />

    @if ($editingUuid)
        <div class="mb-6 space-y-4 rounded-xl border border-teal-border bg-white p-6 shadow-sm dark:bg-zinc-900">
            <flux:heading size="sm" class="text-teal-deep!">{{ __('Editar institución') }}</flux:heading>

            <form wire:submit="save" class="space-y-6">
                @include('livewire.institutions._form-fields')

                <div class="flex gap-2">
                    <flux:button variant="primary" type="submit" class="bg-teal! hover:bg-teal-deep!">{{ __('Guardar') }}</flux:button>
                    <flux:button wire:click="$set('editingUuid', null)">{{ __('Cancelar') }}</flux:button>
                </div>
            </form>
        </div>
    @endif

    @if ($assigningUuid)
        <div class="mb-6 space-y-4 rounded-xl border border-amber/30 bg-amber-bg p-6">
            <flux:heading size="sm" class="text-brand-text!">{{ __('Asignar administrador') }}</flux:heading>

            <form wire:submit="saveAdmin" class="space-y-4">
                <flux:input wire:model="admin_name" :label="__('Nombre')" />
                <flux:input wire:model="admin_email" type="email" :label="__('Correo')" />

                <div class="flex gap-2">
                    <flux:button variant="primary" type="submit" class="bg-teal! hover:bg-teal-deep!">{{ __('Asignar') }}</flux:button>
                    <flux:button wire:click="$set('assigningUuid', null)">{{ __('Cancelar') }}</flux:button>
                </div>
            </form>
        </div>
    @endif

    <div
        wire:key="institutions-grid-{{ $this->institutionsCacheKey }}"
        x-data="{
            selected: null,
            details: @js($this->institutionDetails),
            entityTypeLabels: @js(['public' => __('Pública'), 'private' => __('Privada')]),
            frequencyLabels: @js(['weekly' => __('Semanal'), 'biweekly' => __('Quincenal'), 'monthly' => __('Mensual'), 'disabled' => __('Deshabilitado')]),
            show(uuid) { this.selected = this.details[uuid]; $dispatch('modal-show', { name: 'institution-detail' }); },
        }"
    >
    <div class="grid gap-5 sm:grid-cols-2">
        @foreach ($this->institutions as $institution)
            <div wire:key="institution-{{ $institution->uuid }}" class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition hover:border-teal-border hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-start justify-between gap-3">
                    <div>
                        <button type="button" x-on:click="show('{{ $institution->uuid }}')" class="text-left">
                            <flux:heading size="lg" class="text-teal-deep! hover:underline dark:text-teal!">{{ $institution->name }}</flux:heading>
                        </button>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ $institution->nit ?? __('Sin NIT') }} · {{ $institution->address ?? __('Sin dirección') }}
                        </flux:text>
                        @if ($institution->phone)
                            <flux:text class="text-sm text-brand-text-muted!">{{ $institution->phone }}</flux:text>
                        @endif
                    </div>

                    <span class="whitespace-nowrap rounded-full bg-teal-bg px-3 py-1 text-xs font-bold tracking-wide text-teal-deep uppercase">
                        {{ __(ucfirst($institution->bulletin_frequency)) }}
                    </span>
                </div>

                <div class="mb-4 flex gap-2">
                    <div class="flex-1 rounded-lg bg-teal-bg px-3 py-2 text-center">
                        <div class="text-lg font-extrabold text-teal-deep">{{ $institution->active_student_count }}</div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Estudiantes') }}</div>
                    </div>
                    <div class="flex-1 rounded-lg bg-teal-bg px-3 py-2 text-center">
                        <div class="text-lg font-extrabold text-teal-deep">{{ $institution->active_teacher_count }}</div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Profesores') }}</div>
                    </div>
                    <div class="flex-1 rounded-lg bg-amber-bg px-3 py-2 text-center">
                        <div class="text-lg font-extrabold text-amber">{{ $institution->groups_count }}</div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Grupos') }}</div>
                    </div>
                </div>

                <flux:text class="mb-4 text-sm text-brand-text-muted!">
                    {{ __('Admin:') }}
                    <span class="font-semibold text-brand-text">{{ $institution->admin?->name ?? __('Sin asignar') }}</span>
                    @if ($institution->admin)
                        <span class="text-brand-text-muted">({{ $institution->admin->email }})</span>
                    @endif
                </flux:text>

                <div class="flex flex-wrap gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                    <flux:button size="sm" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show('{{ $institution->uuid }}')" />

                    <flux:button size="sm" icon="academic-cap" href="{{ route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'student']) }}" wire:navigate>
                        {{ __('Estudiantes') }}
                    </flux:button>
                    <flux:button size="sm" icon="user-group" href="{{ route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'teacher']) }}" wire:navigate>
                        {{ __('Profesores') }}
                    </flux:button>
                    <flux:button size="sm" icon="squares-2x2" href="{{ route('institutions.show', ['institution' => $institution->uuid, 'tab' => 'group']) }}" wire:navigate>
                        {{ __('Grupos') }}
                    </flux:button>

                    @can('update', $institution)
                        <flux:button size="sm" icon="pencil-square" wire:click="edit('{{ $institution->uuid }}')">{{ __('Editar') }}</flux:button>
                    @endcan

                    @can('assignAdmin', $institution)
                        @if (! $institution->admin)
                            <flux:button size="sm" icon="user-plus" wire:click="assign('{{ $institution->uuid }}')">{{ __('Asignar admin') }}</flux:button>
                        @endif
                    @endcan

                    @can('create', \App\Models\Challenge::class)
                        <flux:button size="sm" icon="sparkles" class="text-amber! hover:bg-amber-bg!" href="{{ route('challenges.manage.create', ['institution' => $institution->uuid]) }}" wire:navigate>
                            {{ __('Crear reto') }}
                        </flux:button>
                    @endcan

                    @can('delete', $institution)
                        <flux:button size="sm" variant="danger" icon="trash" wire:click="delete('{{ $institution->uuid }}')" wire:confirm="{{ __('¿Eliminar esta institución?') }}" class="cursor-pointer">
                            {{ __('Eliminar') }}
                        </flux:button>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    @if ($this->institutions->isEmpty())
        <flux:text class="py-10 text-center text-brand-text-muted!">{{ __('No se encontraron instituciones con esos criterios.') }}</flux:text>
    @endif

    @if ($this->institutions->hasPages())
        <div class="mt-6">
            {{ $this->institutions->links() }}
        </div>
    @endif

    <flux:modal name="institution-detail" class="w-full max-w-2xl">
        <template x-if="selected">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg" x-text="selected.name"></flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!" x-text="selected.nit || '{{ __('Sin NIT') }}'"></flux:text>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm sm:grid-cols-3 dark:bg-zinc-800/50">
                    <div class="col-span-2 sm:col-span-3">
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Dirección') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.address || '—'"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Teléfono') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.phone || '—'"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Ubicación') }}</div>
                        <div class="font-medium text-brand-text" x-text="[selected.city, selected.state, selected.country].filter(Boolean).join(', ') || '—'"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Tipo de entidad') }}</div>
                        <div class="font-medium text-brand-text" x-text="entityTypeLabels[selected.entity_type] || '—'"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Boletines') }}</div>
                        <div class="font-medium text-brand-text" x-text="frequencyLabels[selected.bulletin_frequency]"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Estudiantes activos') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.active_student_count"></div>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Profesores activos') }}</div>
                        <div class="font-medium text-brand-text" x-text="selected.active_teacher_count"></div>
                    </div>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('Contacto de la institución') }}</flux:heading>
                    <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Nombre') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.contact_name || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Documento') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.contact_document || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Correo') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.contact_email || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Celular') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.contact_phone || '—'"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('Rector o director') }}</flux:heading>
                    <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Nombre') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.principal_name || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Documento') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.principal_document || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Ingreso') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.principal_started_at || '—'"></div>
                        </div>
                    </div>
                </div>

                <div>
                    <flux:heading size="sm" class="mb-2">{{ __('Administrador de la plataforma') }}</flux:heading>
                    <flux:text x-show="selected.admin_name" x-text="selected.admin_name + ' (' + selected.admin_email + ')'"></flux:text>
                    <flux:text x-show="!selected.admin_name" class="text-brand-text-muted!">{{ __('Sin asignar') }}</flux:text>
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
