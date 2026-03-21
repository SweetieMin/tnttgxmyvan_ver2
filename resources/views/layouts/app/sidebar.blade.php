<!DOCTYPE html>
@php($themePreset = \App\Models\Setting::query()->where('key', 'theme.preset')->first()?->value ?? 'sky')
@php($themeNeutralPalette = \App\Models\Setting::query()->where('key', 'theme.neutral_palette')->first()?->value ?? 'gray')
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body x-data="{ themePreset: '{{ $themePreset }}', themeNeutralPalette: '{{ $themeNeutralPalette }}' }" x-on:theme-preset-updated.window="themePreset = $event.detail.preset"
    x-on:theme-neutral-palette-updated.window="themeNeutralPalette = $event.detail.neutralPalette"
    :data-theme="themePreset" :data-neutral-palette="themeNeutralPalette"
    class="min-h-screen overflow-hidden bg-white antialiased dark:bg-zinc-800">
    <flux:sidebar sticky collapsible
        class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 w-75 data-flux-sidebar-collapsed-desktop:w-15 z-10">
        <flux:sidebar.header>
            <flux:sidebar.brand href="{{ route('dashboard') }}" logo="/storage/images/sites/FAVICON_default.png"
                logo:dark="/storage/images/sites/FAVICON_default.png" name="Đoàn TNTT Gx Mỹ Vân" wire:navigate
                alt='Đoàn TNTT Gx Mỹ Vân' />
            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2 hidden lg:flex" />
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
                <flux:sidebar.item icon="cog" :href="route('admin.settings.site.general')"
                    :current="request()->routeIs('admin.settings.site.*')" wire:navigate>
                    {{ __('System configuration') }}
                </flux:sidebar.item>
            @endcan

            @canany(['settings.log.activity.view'])
                <flux:sidebar.item icon="notebook-pen" :href="route('admin.settings.log.activity')"
                    :current="request()->routeIs('admin.settings.log.activity')" wire:navigate>
                    {{ __('System logs') }}
                </flux:sidebar.item>
            @endcan
        </flux:sidebar.nav>

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
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

    @fluxScripts
</body>

</html>
