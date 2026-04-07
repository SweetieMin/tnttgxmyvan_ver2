<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <flux:skeleton.group animate="pulse">
        <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 md:px-6">
            <flux:skeleton class="h-6 w-32 rounded-lg" />

            <div class="flex items-center gap-3">
                <flux:skeleton class="h-10 w-40 rounded-xl" />
                <flux:skeleton class="h-10 w-10 rounded-xl" />
            </div>
        </div>

        <div class="border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 md:px-6">
            <flux:skeleton class="h-10 w-full rounded-xl" />
        </div>

        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @for ($index = 0; $index < 3; $index++)
                <div class="grid grid-cols-1 gap-3 px-4 py-4 md:grid-cols-4 md:gap-6 md:px-6">
                    <flux:skeleton class="h-5 rounded-lg" />
                    <flux:skeleton class="h-5 rounded-lg" />
                    <flux:skeleton class="h-5 rounded-lg" />
                    <flux:skeleton class="h-5 rounded-lg" />
                </div>
            @endfor
        </div>
    </flux:skeleton.group>
</div>
