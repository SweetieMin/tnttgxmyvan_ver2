<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body x-data="{ themePreset: '{{ $themePreset }}', themeNeutralPalette: '{{ $themeNeutralPalette }}' }"
    x-on:theme-preset-updated.window="themePreset = $event.detail.preset"
    x-on:theme-neutral-palette-updated.window="themeNeutralPalette = $event.detail.neutralPalette"
    x-on:livewire:navigating.window=""
    x-on:livewire:navigated.window=""
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

        <div data-app-sidebar-scroll="true" data-app-sidebar-scroll-key="app-sidebar-scroll-top"
            class="flex min-h-0 flex-1 flex-col overflow-y-auto">
            @foreach ($sidebarNavigation['primary'] ?? [] as $section)
                <div class="my-2">
                    <flux:separator :text="$section['label']" />
                </div>

                <flux:sidebar.nav>
                    @foreach ($section['items'] as $item)
                        <flux:sidebar.item :icon="$item['icon']" :href="$item['href']" :current="$item['active']"
                            wire:navigate>
                            {{ $item['label'] }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.nav>
            @endforeach

            <flux:spacer />

            @foreach ($sidebarNavigation['secondary'] ?? [] as $section)
                <flux:separator :text="$section['label']" />
                <flux:sidebar.nav>
                    @foreach ($section['items'] as $item)
                        <flux:sidebar.item :icon="$item['icon']" :href="$item['href']" :current="$item['active']"
                            wire:navigate>
                            {{ $item['label'] }}
                        </flux:sidebar.item>
                    @endforeach
                </flux:sidebar.nav>
            @endforeach
        </div>

        <div class="shrink-0 hidden lg:block">
            <x-desktop-user-menu :name="$sidebarUserName" :email="$sidebarUserEmail" :user-picture="$sidebarUserPicture" />
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

            <x-desktop-user-menu :name="$sidebarUserName" :email="$sidebarUserEmail" :user-picture="$sidebarUserPicture" />

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

    <flux:main
        class="h-[calc(100dvh-57px)] overflow-y-auto p-4! pb-[calc(env(safe-area-inset-bottom)+1rem)] md:pb-4">
        {{ $slot }}
    </flux:main>

    <livewire:notifications />

    <flux:toast.group position="top end">
        <flux:toast />
    </flux:toast.group>

    @filamentScripts
    @fluxScripts
    @livewireCalendarScripts
</body>

</html>
