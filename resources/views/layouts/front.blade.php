<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    @include('partials.head')
</head>

<body class="flex min-h-screen flex-col bg-gradient-to-b from-amber-50 via-white to-zinc-100 text-zinc-900 antialiased">
    <header class="px-4 pt-2 md:px-6 ">
        <div class="mx-auto w-full max-w-4xl">
            <flux:card class="flex items-center justify-between gap-4 rounded-3xl border border-amber-200/80 bg-white/90 px-5 py-4 shadow-sm shadow-amber-100 backdrop-blur md:px-6">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3 transition-opacity hover:opacity-90">
                    <img
                        src="{{ asset('storage/' . ltrim($siteFavicon ?: $siteLogo ?: 'images/sites/FAVICON_default.png', '/')) }}"
                        alt="{{ filled($siteTitle) ? $siteTitle : config('app.name', 'Laravel') }}"
                        class="size-12 rounded-2xl object-contain md:size-14"
                    />
                    <div class="min-w-0">
                        <flux:heading size="lg" class="truncate md:text-xl">
                            {{ filled($siteTitle) ? $siteTitle : config('app.name', 'Laravel') }}
                        </flux:heading>
                    </div>
                </a>

                @auth
                    <flux:button :href="route('dashboard')" icon="arrow-right-start-on-rectangle">
                        {{ __('Go to dashboard') }}
                    </flux:button>
                @else
                    <flux:button :href="route('login')" icon="arrow-right-start-on-rectangle">
                        {{ __('Log in') }}
                    </flux:button>
                @endauth
            </flux:card>
        </div>
    </header>

    <main class="flex-1 px-4 py-4 md:px-6 md:py-6">
        <div class="mx-auto w-full max-w-4xl">
            {{ $slot }}
        </div>
    </main>

    <footer class="px-4 pb-2 md:px-6 md:pb-4">
        <div class="mx-auto w-full max-w-4xl">
            <flux:card class="flex flex-col gap-3 rounded-3xl border border-amber-200/80 bg-white/80 px-5 py-4 text-sm text-zinc-600 shadow-sm shadow-amber-100 backdrop-blur md:flex-row md:items-center md:justify-between md:px-6">
                <flux:text class="text-center text-zinc-500">
                &copy; {{ now()->year }} {{ filled($siteTitle) ? $siteTitle : config('app.name', 'Laravel') }}
                </flux:text>
            </flux:card>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
