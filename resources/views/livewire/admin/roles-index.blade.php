<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Roles') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Roles de la plataforma y cuántos usuarios/permisos tiene cada uno.') }}</flux:text>
        </div>

        <flux:button variant="ghost" icon="key" href="{{ route('admin.permissions.index') }}" wire:navigate>{{ __('Ver permisos') }}</flux:button>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @foreach ($roles as $role)
                <div wire:key="role-{{ $role->id }}" class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                    <div>
                        <flux:heading size="sm">{{ $role->name }}</flux:heading>
                        <flux:text class="text-sm text-brand-text-muted!">
                            {{ trans_choice(':count usuario|:count usuarios', $role->users_count, ['count' => $role->users_count]) }}
                            ·
                            {{ trans_choice(':count permiso|:count permisos', $role->permissions_count, ['count' => $role->permissions_count]) }}
                        </flux:text>
                    </div>

                    <flux:button size="sm" icon="adjustments-horizontal" href="{{ route('admin.roles.permissions', $role) }}" wire:navigate>
                        {{ __('Permisos') }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    </div>
</section>
