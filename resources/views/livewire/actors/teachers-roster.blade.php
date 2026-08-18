<section class="w-full">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Profesores') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Matrículas activas de profesores en tu institución.') }}</flux:text>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <flux:button icon="user-plus" variant="primary" href="{{ route('actors.teachers.create') }}" wire:navigate>{{ __('Nuevo profesor') }}</flux:button>
            <flux:button icon="arrow-up-tray" href="{{ route('actors.teachers.import') }}" wire:navigate>{{ __('Cargar profesores') }}</flux:button>
        </div>
    </div>

    <div class="mb-4 flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700 dark:bg-zinc-900">
        <livewire:actors.reenroll-member role="teacher" :key="'reenroll-teacher'" />
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o número de documento...')" class="sm:max-w-xs" />
    </div>

    <div
        wire:key="teachers-{{ $this->membershipsCacheKey }}"
        x-data="{
            selected: null,
            details: @js($this->membershipDetails),
            documentTypeLabels: {
                cedula_ciudadania: '{{ __('Cédula de ciudadanía') }}',
                cedula_extranjeria: '{{ __('Cédula de extranjería') }}',
                pasaporte: '{{ __('Pasaporte') }}',
            },
            show(id) { this.selected = this.details[id]; $dispatch('modal-show', { name: 'membership-detail' }); },
        }"
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @forelse ($this->memberships as $membership)
                <div wire:key="membership-{{ $membership->id }}" class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-teal-bg text-sm font-bold text-teal-deep">
                            {{ $membership->user->initials() }}
                        </span>
                        <div class="min-w-0">
                            <button type="button" x-on:click="show({{ $membership->id }})" class="text-left font-semibold text-brand-text hover:text-teal-deep hover:underline">
                                {{ $membership->user->name }}
                            </button>
                            <flux:text class="block truncate text-sm text-brand-text-muted!">
                                {{ $membership->user->document_number ?? __('Sin documento') }}
                                @if ($membership->user->phone)
                                    · {{ $membership->user->phone }}
                                @endif
                            </flux:text>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @forelse ($membership->user->teacherGroups as $group)
                            <span wire:key="membership-{{ $membership->id }}-group-{{ $group->id }}" class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase">{{ $group->name }}</span>
                        @empty
                            <span class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-bold text-zinc-500 uppercase dark:bg-zinc-800">{{ __('Sin grupos') }}</span>
                        @endforelse
                        <span class="hidden whitespace-nowrap rounded-full bg-teal-bg px-3 py-1 text-xs font-bold text-teal-deep uppercase sm:inline-block">
                            {{ __('Desde :date', ['date' => $membership->started_at->format('d/m/Y')]) }}
                        </span>
                        <div class="ml-1 flex items-center gap-1 border-l border-zinc-200 pl-2 dark:border-zinc-700">
                            <flux:button size="sm" variant="ghost" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show({{ $membership->id }})" />
                            <flux:button size="sm" variant="ghost" icon="pencil-square" :tooltip="__('Editar')" href="{{ route('actors.teachers.edit', $membership->user) }}" wire:navigate />
                            <livewire:actors.manage-membership-groups :membership="$membership" :key="'groups-'.$membership->id" />
                            <livewire:actors.withdraw-membership :membership="$membership" :key="$membership->id" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center gap-3 px-6 py-16 text-center">
                    <span class="flex size-14 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                        <flux:icon icon="user-group" variant="outline" class="size-7" />
                    </span>
                    <flux:text class="text-brand-text-muted!">{{ __('No hay profesores matriculados en tu institución.') }}</flux:text>
                    <flux:button icon="user-plus" href="{{ route('actors.teachers.create') }}" wire:navigate>{{ __('Nuevo profesor') }}</flux:button>
                </div>
            @endforelse
        </div>

        @if ($this->memberships->hasPages())
            <div class="border-t border-zinc-100 px-5 py-3 dark:border-zinc-800">
                {{ $this->memberships->links() }}
            </div>
        @endif

        <flux:modal name="membership-detail" :dismissible="false" class="w-full max-w-lg">
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
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Matriculado desde') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.started_at"></div>
                        </div>
                    </div>

                    <div>
                        <flux:heading size="sm" class="mb-2">{{ __('Grupos a cargo') }}</flux:heading>
                        <div class="flex flex-wrap gap-1" x-show="selected.groups.length">
                            <template x-for="groupName in selected.groups" :key="groupName">
                                <span class="rounded-full bg-amber-bg px-3 py-1 text-xs font-bold text-amber uppercase" x-text="groupName"></span>
                            </template>
                        </div>
                        <flux:text x-show="!selected.groups.length" class="text-brand-text-muted!">{{ __('Sin grupos asignados.') }}</flux:text>
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
