<section class="mx-auto w-full max-w-2xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ route('admin.users.index') }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ $editingId ? __('Editar usuario') : __('Crear usuario interno') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Usuarios de plataforma (super admin, manager, pedagogo), no ligados a ninguna institución.') }}
            </flux:text>
        </div>
    </div>

    <form wire:submit="store" class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-5 flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                    <flux:icon icon="identification" variant="micro" class="size-5" />
                </span>
                <div>
                    <flux:heading size="lg">{{ __('Datos del usuario') }}</flux:heading>
                    <flux:text class="text-sm text-brand-text-muted!">{{ __('Información personal y rol de plataforma.') }}</flux:text>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="first_name" :label="__('Nombres')" />
                    <flux:input wire:model="last_name" :label="__('Apellidos')" />
                </div>
                <div class="grid grid-cols-1 items-start gap-4 sm:grid-cols-2">
                    <flux:input wire:model="email" type="email" :label="__('Correo (usuario de acceso)')" />
                    <flux:select wire:model="role" :label="__('Rol')">
                        <flux:select.option value="">{{ __('Selecciona...') }}</flux:select.option>
                        <flux:select.option value="super_admin">{{ __('Super admin') }}</flux:select.option>
                        <flux:select.option value="manager">{{ __('Manager') }}</flux:select.option>
                        <flux:select.option value="pedagogue">{{ __('Pedagogo') }}</flux:select.option>
                    </flux:select>
                </div>
                <flux:text class="text-sm text-brand-text-muted!">
                    @unless ($editingId)
                        {{ __('Se enviará un correo al usuario para que cree su propia contraseña.') }}
                    @endunless
                </flux:text>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <flux:button variant="ghost" href="{{ route('admin.users.index') }}" wire:navigate>{{ __('Cancelar') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ $editingId ? __('Guardar cambios') : __('Crear usuario') }}</flux:button>
        </div>
    </form>
</section>
