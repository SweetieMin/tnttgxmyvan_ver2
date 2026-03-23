<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body x-data="{ themePreset: '{{ $themePreset }}', themeNeutralPalette: '{{ $themeNeutralPalette }}' }" x-on:theme-preset-updated.window="themePreset = $event.detail.preset"
    x-on:theme-neutral-palette-updated.window="themeNeutralPalette = $event.detail.neutralPalette"
    :data-theme="themePreset" :data-neutral-palette="themeNeutralPalette"
    class="min-h-screen overflow-hidden bg-white antialiased dark:bg-zinc-800">
    <flux:sidebar sticky collapsible
        class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 w-75 data-flux-sidebar-collapsed-desktop:w-15 z-10 flex h-screen flex-col overflow-hidden">
        <flux:sidebar.header>
            <flux:sidebar.brand href="{{ route('dashboard') }}"
                logo="{{ asset('storage/' . ltrim($siteFavicon ?: 'images/sites/FAVICON_default.png', '/')) }}"
                logo:dark="{{ asset('storage/' . ltrim($siteFavicon ?: 'images/sites/FAVICON_default.png', '/')) }}"
                name="{{ $siteTitle }}" wire:navigate alt="{{ $siteTitle }}" />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2 hidden lg:flex" />
        </flux:sidebar.header>

        <div class="flex min-h-0 flex-1 flex-col overflow-y-auto">
            <flux:separator :text="__('General')" />
            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            @canany(['management.academic-year.view', 'management.program.view', 'management.regulation.view'])
                <flux:separator :text="__('Management')" />
                <flux:sidebar.nav>
                    @can('management.academic-year.view')
                        <flux:sidebar.item icon="calendar-days" :href="route('admin.management.academic-years')"
                            :current="request()->routeIs('admin.management.academic-years')" wire:navigate>
                            {{ __('Academic years') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('management.program.view')
                        <flux:sidebar.item icon="academic-cap" :href="route('admin.management.programs')"
                            :current="request()->routeIs('admin.management.programs')" wire:navigate>
                            {{ __('Programs') }}
                        </flux:sidebar.item>
                    @endcan
                    @can('management.regulation.view')
                        <flux:sidebar.item icon="clipboard-document-list" :href="route('admin.management.regulations')"
                            :current="request()->routeIs('admin.management.regulations')" wire:navigate>
                            {{ __('Regulations') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.nav>
            @endcanany

            @canany(['finance.transaction.view', 'finance.category.view'])
                <flux:sidebar.nav>
                    <flux:separator :text="__('Finance')" />

                    @can('finance.category.view')
                        <flux:sidebar.item icon="tag" :href="route('admin.finance.categories')"
                            :current="request()->routeIs('admin.finance.categories*')" wire:navigate>
                            {{ __('Categories') }}
                        </flux:sidebar.item>
                    @endcan

                    @can('finance.transaction.view')
                        <flux:sidebar.item icon="banknotes" :href="route('admin.finance.transactions')"
                            :current="request()->routeIs('admin.finance.transactions*')" wire:navigate>
                            {{ __('Common fund') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.nav>
            @endcanany

            @canany(['access.role.view', 'access.permission.view'])
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
            @endcanany

            <flux:spacer />

            @canany(['settings.site.general.view', 'settings.site.theme.view', 'settings.log.activity.view',
                'settings.log.activity-failed.view', 'settings.site.email.view', 'settings.site.maintenance.view',
                'settings.log.system.view'])
                <flux:separator :text="__('Advance')" />
                <flux:sidebar.nav>
                    @can('settings.site.general.view')
                        <flux:sidebar.item icon="cog" :href="route('admin.settings.site.general')"
                            :current="request()->routeIs('admin.settings.site.*')" wire:navigate>
                            {{ __('System configuration') }}
                        </flux:sidebar.item>
                    @endcan

                    @canany(['settings.log.activity.view', 'settings.log.activity-failed.view'])
                        <flux:sidebar.item icon="notebook-pen" :href="route('admin.settings.log.activity')"
                            :current="request()->routeIs('admin.settings.log.*')" wire:navigate>
                            {{ __('System logs') }}
                        </flux:sidebar.item>
                    @endcan
                </flux:sidebar.nav>
            @endcanany
        </div>

        <div class="shrink-0">
            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </div>
    </flux:sidebar>

    <flux:header
        class="sticky top-0 block! h-14 bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:navbar class="lg:hidden h-full w-full items-center px-3">

            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />

            <flux:button variant="ghost" x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon"
                aria-label="Toggle dark mode" />
            <flux:separator vertical class="my-2" />

            <x-desktop-user-menu :name="auth()->user()->name" />

        </flux:navbar>
        <flux:navbar scrollable class="hidden lg:flex h-full items-center gap-2">

            <flux:breadcrumbs>

            </flux:breadcrumbs>

            <flux:sidebar.spacer />

            <div class="flex h-full min-w-100 items-center">

            </div>

            <flux:separator vertical class="my-2" />

            <flux:button variant="ghost" x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon"
                aria-label="Toggle dark mode" />
            <flux:separator vertical class="my-2" />


        </flux:navbar>
    </flux:header>

    <flux:main class="h-[calc(100vh-57px)] overflow-y-auto p-4!">
        {{ $slot }}
    </flux:main>

    <flux:toast.group position="top end">
        <flux:toast />
    </flux:toast.group>

    @fluxScripts
</body>

</html>
