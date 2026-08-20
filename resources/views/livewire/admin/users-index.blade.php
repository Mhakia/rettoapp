<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Usuarios internos') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Super admins, managers y pedagogos de la plataforma.') }}</flux:text>
        </div>

        @can('create', \App\Models\User::class)
            <flux:button variant="primary" icon="plus" class="bg-teal! hover:bg-teal-deep!" href="{{ route('admin.users.create') }}" wire:navigate>{{ __('Crear usuario') }}</flux:button>
        @endcan
    </div>

    <div class="mb-6 flex flex-wrap gap-3">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Buscar por nombre o correo...')" class="max-w-md" />

        <flux:select wire:model.live="role" class="max-w-xs">
            <flux:select.option value="">{{ __('Todos los roles') }}</flux:select.option>
            <flux:select.option value="super_admin">{{ __('Super admin') }}</flux:select.option>
            <flux:select.option value="manager">{{ __('Manager') }}</flux:select.option>
            <flux:select.option value="pedagogue">{{ __('Pedagogo') }}</flux:select.option>
        </flux:select>
    </div>

    <div
        wire:key="users-grid-{{ $this->usersCacheKey }}"
        x-data="{
            selected: null,
            details: @js($this->userDetails),
            show(uuid) { this.selected = this.details[uuid]; $dispatch('modal-show', { name: 'user-detail' }); },
        }"
    >
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->users as $user)
                    <div wire:key="user-{{ $user->uuid }}" class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$user->name" :initials="$user->initials()" />
                            <div>
                                <button type="button" x-on:click="show('{{ $user->uuid }}')" class="text-left">
                                    <flux:heading size="sm" class="hover:underline">{{ $user->name }}</flux:heading>
                                </button>
                                <flux:text class="text-sm text-brand-text-muted!">{{ $user->email }}</flux:text>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="whitespace-nowrap rounded-full bg-teal-bg px-3 py-1 text-xs font-bold tracking-wide text-teal-deep uppercase">
                                {{ $user->roles->pluck('name')->implode(', ') }}
                            </span>

                            <flux:button size="sm" icon="eye" :tooltip="__('Ver detalle')" x-on:click="show('{{ $user->uuid }}')" />

                            @can('update', $user)
                                <flux:button size="sm" icon="pencil-square" :tooltip="__('Editar')" href="{{ route('admin.users.edit', $user) }}" wire:navigate />
                            @endcan

                            @can('delete', $user)
                                <flux:button size="sm" variant="danger" icon="user-minus" :tooltip="__('Desactivar')" wire:click="deactivate('{{ $user->uuid }}')" wire:confirm="{{ __('¿Desactivar este usuario? Podrá reactivarse más adelante.') }}" />
                            @endcan
                        </div>
                    </div>
                @empty
                    <flux:text class="block px-6 py-10 text-center text-brand-text-muted!">{{ __('No se encontraron usuarios con esos criterios.') }}</flux:text>
                @endforelse
            </div>
        </div>

        @if ($this->users->hasPages())
            <div class="mt-6">
                {{ $this->users->links() }}
            </div>
        @endif

        <flux:modal name="user-detail" :dismissible="false" class="w-full max-w-md">
            <template x-if="selected">
                <div class="space-y-4">
                    <div>
                        <flux:heading size="lg" x-text="selected.name"></flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!" x-text="selected.email"></flux:text>
                    </div>

                    <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-4 text-sm dark:bg-zinc-800/50">
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Rol') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.role"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Creado') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.createdAt"></div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-xs font-semibold text-brand-text-muted uppercase">{{ __('Correo verificado') }}</div>
                            <div class="font-medium text-brand-text" x-text="selected.verified ? '{{ __('Sí') }}' : '{{ __('No') }}'"></div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <flux:button variant="ghost" x-on:click="$dispatch('modal-close', { name: 'user-detail' })">{{ __('Cerrar') }}</flux:button>
                    </div>
                </div>
            </template>
        </flux:modal>
    </div>
</section>
