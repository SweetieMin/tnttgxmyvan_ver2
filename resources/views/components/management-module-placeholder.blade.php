@props([
    'heading',
    'description' => null,
    'highlights' => [],
])

<flux:card class="rounded-2xl p-6">
    <div class="space-y-5">
        <div class="space-y-2">
            <flux:heading size="lg">{{ $heading }}</flux:heading>

            @if (filled($description))
                <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $description }}</flux:text>
            @endif
        </div>

        @if ($highlights !== [])
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($highlights as $highlight)
                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $highlight }}</flux:text>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50/80 p-4 dark:border-white/10 dark:bg-white/5">
            <flux:text class="text-zinc-600 dark:text-zinc-300">
                {{ __('This module has been scaffolded and is ready for the next implementation step.') }}
            </flux:text>
        </div>
    </div>
</flux:card>
