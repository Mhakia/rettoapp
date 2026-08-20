<section class="mx-auto w-full max-w-3xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.roles.index') }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Permisos de :role', ['role' => $roleName]) }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Selecciona los permisos que tendrá este rol.') }}</flux:text>
        </div>
    </div>

    @if ($isSuperAdminRole)
        <div class="mb-6 rounded-xl border border-amber/30 bg-amber-bg px-6 py-4">
            <flux:text class="text-brand-text!">
                {{ __('Por seguridad, los permisos del rol super_admin no se pueden editar desde esta pantalla (evita quedarte sin acceso por error).') }}
            </flux:text>
        </div>
    @endif

    <div class="space-y-6">
        @foreach ($this->groupedPermissions as $group => $permissions)
            <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-3 capitalize">{{ str_replace('-', ' ', $group) }}</flux:heading>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-1">
                    @foreach ($permissions as $permission)
                        <label wire:key="permission-{{ $permission->id }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 select-none hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                            <flux:checkbox wire:model="permission_names" value="{{ $permission->name }}" :disabled="$isSuperAdminRole" />
                            <span class="text-sm text-brand-text" x-on:click="$el.previousElementSibling.click()">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @unless ($isSuperAdminRole)
        <div class="mt-6 flex justify-end gap-2">
            <flux:button variant="ghost" href="{{ route('admin.roles.index') }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" wire:click="save" wire:confirm="{{ __('¿Guardar los permisos de este rol? Esto cambia de inmediato lo que pueden hacer sus usuarios.') }}">
                {{ __('Guardar permisos') }}
            </flux:button>
        </div>
    @endunless
</section>
