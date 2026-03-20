<x-layouts::app.sidebar :title="$title ?? null">
    
        {{ $slot }}

    <flux:toast.group position="top end">
        <flux:toast />
    </flux:toast.group>
</x-layouts::app.sidebar>
