<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
    <div
        class="relative grid min-h-dvh flex-col items-center justify-center px-4 sm:px-6 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="absolute top-4 right-4 z-50">
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle"
                aria-label="Toggle dark mode" />
        </div>
        <div
            class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
            <div class="absolute inset-0 bg-neutral-900"></div>
            <a href="{{ route('home') }}" class="relative z-20 flex items-center text-lg font-medium" wire:navigate>
                <span class="flex h-10 w-10 items-center justify-center rounded-md">
                    <x-app-logo-icon class="me-2 h-7 fill-current text-white" />
                </span>
                Đoàn TNTT Gx Mỹ Vân
            </a>

            @php
                [$text, $ref] = str(App\Foundation\BibleVerseKids::verses()->random())->explode('-');
            @endphp

            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-2">
                    <flux:heading class="text-white" size="lg">&ldquo;{{ trim($text) }}&rdquo;</flux:heading>
                    <footer>
                        <flux:heading class="text-white">{{ trim($ref) }}</flux:heading>
                    </footer>
                </blockquote>
            </div>
        </div>

        <div class="w-full py-6 lg:p-8">
            <div class="mx-auto flex w-full max-w-[480px] flex-col justify-center space-y-5">

                <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden"
                    wire:navigate>
                    <span class="flex h-9 w-9 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>

                    <span class="sr-only">Đoàn TNTT Gx Mỹ Vân</span>
                </a>
                <flux:card class="space-y-6 p-5 sm:p-6">
                    {{ $slot }}
                </flux:card>
            </div>
        </div>
    </div>
    @fluxScripts
</body>

</html>
