<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-zinc-100 text-zinc-900 antialiased">
    <main class="min-h-screen px-4 py-8 md:px-6 md:py-12">
        <div class="mx-auto w-full max-w-4xl">
            {{ $slot }}
        </div>
    </main>

    @fluxScripts
</body>

</html>
