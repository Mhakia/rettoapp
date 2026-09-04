<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @can('view-institutions')
                        <flux:sidebar.item icon="building-office" :href="route('institutions.index')" :current="request()->routeIs('institutions.*')" wire:navigate>
                            {{ __('Instituciones') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.group>

                @role('super_admin')
                    <x-sidebar-nav-group :heading="__('Administración')" :items="[
                        ['icon' => 'shield-check', 'route' => 'admin.users.index', 'current' => 'admin.users.*', 'label' => __('Usuarios internos')],
                        ['icon' => 'user-group', 'route' => 'admin.roles.index', 'current' => 'admin.roles.*', 'label' => __('Roles')],
                        ['icon' => 'key', 'route' => 'admin.permissions.index', 'current' => 'admin.permissions.*', 'label' => __('Permisos')],
                    ]" />

                    <x-sidebar-nav-group :heading="__('Facturación')" :items="[
                        ['icon' => 'document-text', 'route' => 'billing.plans.index', 'current' => 'billing.plans.*', 'label' => __('Planes')],
                        ['icon' => 'credit-card', 'route' => 'billing.subscriptions.index', 'current' => 'billing.subscriptions.*', 'label' => __('Suscripciones')],
                        ['icon' => 'receipt-refund', 'route' => 'billing.invoices.index', 'current' => 'billing.invoices.*', 'label' => __('Facturas')],
                    ]" />
                @endrole

                @role('institution_admin')
                    <x-sidebar-nav-group :heading="__('Mi institución')" :items="[
                        ['icon' => 'academic-cap', 'route' => 'actors.students.index', 'current' => 'actors.students.*', 'label' => __('Estudiantes')],
                        ['icon' => 'user-group', 'route' => 'actors.teachers.index', 'current' => 'actors.teachers.*', 'label' => __('Profesores')],
                        ['icon' => 'heart', 'route' => 'actors.guardians.index', 'current' => 'actors.guardians.*', 'label' => __('Acudientes')],
                    ]" />
                @endrole

                @can('view-institution-members')
                    <x-sidebar-nav-group :heading="__('Directorio')" :items="[
                        ['icon' => 'academic-cap', 'route' => 'directory.students', 'current' => 'directory.students', 'label' => __('Estudiantes')],
                        ['icon' => 'user-group', 'route' => 'directory.teachers', 'current' => 'directory.teachers', 'label' => __('Profesores')],
                        ['icon' => 'heart', 'route' => 'directory.guardians', 'current' => 'directory.guardians', 'label' => __('Acudientes')],
                    ]" />
                @endcan

                @hasanyrole('institution_admin|teacher')
                    <x-sidebar-nav-group :heading="__('Alertas')" :items="[
                        ['icon' => 'exclamation-triangle', 'route' => 'alerts.index', 'current' => 'alerts.*', 'label' => __('Alertas')],
                    ]" />
                @endhasanyrole

                @canany(['complete-challenge', 'create-challenge', 'update-challenge', 'verify-challenge', 'view-challenge-statistics'])
                    <flux:sidebar.group :heading="__('Retos')" class="grid">
                        @can('complete-challenge')
                            <flux:sidebar.item icon="sparkles" :href="route('challenges.catalog')" :current="request()->routeIs('challenges.catalog')" wire:navigate>
                                {{ __('Retos') }}
                            </flux:sidebar.item>
                        @endcan

                        @canany(['create-challenge', 'update-challenge'])
                            <flux:sidebar.item icon="clipboard-document-list" :href="route('challenges.manage')" :current="request()->routeIs('challenges.manage*')" wire:navigate>
                                {{ __('Gestionar retos') }}
                            </flux:sidebar.item>
                        @endcanany

                        @can('verify-challenge')
                            <flux:sidebar.item icon="check-badge" :href="route('challenges.verify')" :current="request()->routeIs('challenges.verify')" wire:navigate>
                                {{ __('Verificar retos') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="qr-code" :href="route('class-sessions.index')" :current="request()->routeIs('class-sessions.index')" wire:navigate>
                                {{ __('Sesiones de retos') }}
                            </flux:sidebar.item>
                        @endcan

                        @can('view-challenge-statistics')
                            <flux:sidebar.item icon="chart-bar" :href="route('challenges.statistics')" :current="request()->routeIs('challenges.statistics')" wire:navigate>
                                {{ __('Estadísticas de retos') }}
                            </flux:sidebar.item>
                        @endcan
                    </flux:sidebar.group>
                @endcanany
            </flux:sidebar.nav>

            <flux:spacer />

            {{-- <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav> --}}

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <livewire:guardians.impersonation-banner />

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
