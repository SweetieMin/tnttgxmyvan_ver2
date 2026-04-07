<flux:card class="rounded-2xl border border-zinc-200/80 bg-white/95 p-4 dark:border-zinc-700/80 dark:bg-zinc-900/95">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <flux:heading size="sm">{{ __('Event legend') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Preview event colors before connecting them to regulation rules.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1.5 dark:border-red-900/60 dark:bg-red-950/40">
                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                <flux:text class="text-sm font-medium text-red-700 dark:text-red-300">
                    {{ __('Holy Mass') }}
                </flux:text>
            </div>

            <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 dark:border-amber-900/60 dark:bg-amber-950/40">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                <flux:text class="text-sm font-medium text-amber-700 dark:text-amber-300">
                    {{ __('Eucharistic adoration') }}
                </flux:text>
            </div>
        </div>
    </div>
</flux:card>
