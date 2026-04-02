<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div
        class="relative grid min-h-dvh flex-col items-center justify-center px-2 sm:px-6 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="absolute top-4 right-4 z-50">
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
                aria-label="Toggle dark mode" />
        </div>
        <div
            class="bg-muted relative hidden h-full overflow-hidden p-10 text-white lg:flex lg:flex-col dark:border-e dark:border-neutral-800">
            <img src="{{ asset('storage/' . ltrim($siteLoginImage ?: 'images/sites/login_default.png', '/')) }}"
                alt="Login illustration" class="absolute inset-0 h-full w-full object-cover" />
            <div class="absolute inset-0 bg-linear-to-b from-black/35 via-black/5 to-black/75"></div>

            <div
                class="relative z-20 flex w-full items-center gap-4 rounded-[2rem] border border-white/20 bg-black/28 px-6 py-5 text-lg font-medium text-white shadow-2xl shadow-black/25 backdrop-blur-md">
                <img
                    src="{{ asset('storage/' . ltrim($siteFavicon ?: $siteLogo ?: 'images/sites/FAVICON_default.png', '/')) }}"
                    alt="{{ filled($siteTitle) ? $siteTitle : config('app.name', 'Đoàn TNTT Gx Mỹ Vân') }}"
                    class="size-16 shrink-0 rounded-2xl bg-white/90 object-contain p-1.5 shadow-lg shadow-black/20"
                />
                <div class="min-w-0">
                    <div class="truncate text-2xl font-bold tracking-tight text-white drop-shadow-sm">
                        {{ filled($siteTitle) ? $siteTitle : config('app.name', 'Đoàn TNTT Gx Mỹ Vân') }}
                    </div>
                    @if (filled($siteTagline))
                        <div class="truncate pt-1 text-sm font-medium text-white/85">
                            {{ $siteTagline }}
                        </div>
                    @endif
                </div>
            </div>

            @php
                [$text, $ref] = str(App\Foundation\BibleVerseKids::verses()->random())->explode('-');
            @endphp

            <div class="relative z-20 mt-auto w-full">
                <blockquote class="space-y-2.5 rounded-[2rem] border border-white/15 bg-black/36 px-5 py-5 shadow-2xl shadow-black/25 backdrop-blur-md">

                    <flux:heading class="text-balance text-lg leading-relaxed text-white drop-shadow-sm" size="base">&ldquo;{{ trim($text) }}&rdquo;</flux:heading>
                    <footer class="border-t border-white/15 pt-3">
                        <flux:text class="text-sm font-semibold tracking-wide text-amber-100/90">{{ trim($ref) }}</flux:text>
                    </footer>
                </blockquote>
            </div>
        </div>

        <div class="w-full py-6 lg:px-0 lg:p-8">
            <div
                class="mx-auto flex w-[calc(100vw-5rem)] max-w-none flex-col justify-center space-y-5 sm:w-full lg:max-w-[480px]">

                <div class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden">
                    <img
                        src="{{ asset('storage/' . ltrim($siteFavicon ?: $siteLogo ?: 'images/sites/FAVICON_default.png', '/')) }}"
                        alt="{{ filled($siteTitle) ? $siteTitle : config('app.name', 'Đoàn TNTT Gx Mỹ Vân') }}"
                        class="size-40 rounded-xl object-contain"
                    />

                    <span class="sr-only">{{ filled($siteTitle) ? $siteTitle : config('app.name', 'Đoàn TNTT Gx Mỹ Vân') }}</span>
                </div>
                <div class="w-full">
                    <flux:card class="block w-full space-y-6 p-5 sm:p-6">
                        {{ $slot }}
                    </flux:card>
                </div>
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
