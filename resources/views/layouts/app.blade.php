<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="p-4! lg:p-6!">
        {{ $slot }}
    </flux:main>
    <flux:toast.group position="top end">
        <flux:toast />
    </flux:toast.group>
</x-layouts::app.sidebar>
