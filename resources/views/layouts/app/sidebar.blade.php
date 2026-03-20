<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-zinc-800">
    <flux:sidebar sticky collapsible
        class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 w-75 data-flux-sidebar-collapsed-desktop:w-17 z-10">
        <flux:sidebar.header>
            <flux:sidebar.brand href="{{ route('dashboard') }}" logo="/storage/images/sites/FAVICON_default.png"
                logo:dark="/storage/images/sites/FAVICON_default.png" name="Đoàn TNTT Gx Mỹ Vân" wire:navigate
                alt='Đoàn TNTT Gx Mỹ Vân' />

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:separator :text="__('General')" />
        <flux:sidebar.nav>
            <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                wire:navigate>
                {{ __('Dashboard') }}
            </flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:separator :text="__('Access')" />
        <flux:sidebar.nav>
            @can('access.role.view')
                <flux:sidebar.item icon="shield-check" :href="route('admin.access.roles')"
                    :current="request()->routeIs('admin.access.roles')" wire:navigate>
                    {{ __('Roles') }}
                </flux:sidebar.item>
            @endcan

            @can('access.permission.view')
                <flux:sidebar.item icon="key" :href="route('admin.access.permissions')"
                    :current="request()->routeIs('admin.access.permissions')" wire:navigate>
                    {{ __('Permissions') }}
                </flux:sidebar.item>
            @endcan
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:separator :text="__('Advance')" />
        <flux:sidebar.nav>
            @can('settings.site.general.view')
                <flux:sidebar.item icon="wrench-screwdriver" :href="route('admin.settings.site.general')"
                    :current="request()->routeIs('admin.settings.site.*')" wire:navigate>
                    {{ __('System configuration') }}
                </flux:sidebar.item>
            @endcan
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="start">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>
                    {{ __('Settings') }}
                </flux:menu.item>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>
