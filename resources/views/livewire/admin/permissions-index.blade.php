<section class="w-full">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
        <div>
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Permisos') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">{{ __('Todos los permisos existentes y qué roles los tienen asignados.') }}</flux:text>
        </div>

        <flux:button variant="ghost" icon="user-group" href="{{ route('admin.roles.index') }}" wire:navigate>{{ __('Ver roles') }}</flux:button>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            @foreach ($permissions as $permission)
                <div wire:key="permission-{{ $permission->id }}" class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/60">
                    <flux:text class="font-mono text-sm text-brand-text">{{ $permission->name }}</flux:text>

                    <div class="flex flex-wrap gap-1">
                        @forelse ($permission->roles as $role)
                            <span wire:key="permission-{{ $permission->id }}-role-{{ $role->id }}" class="rounded-full bg-teal-bg px-3 py-1 text-xs font-bold tracking-wide text-teal-deep uppercase">
                                {{ $role->name }}
                            </span>
                        @empty
                            <flux:text class="text-sm text-brand-text-muted!">{{ __('Sin roles asignados') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
