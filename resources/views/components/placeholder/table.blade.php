<div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 md:px-6">
        <div class="h-6 w-32 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>

        <div class="flex items-center gap-3">
            <div class="h-10 w-40 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
            <div class="h-10 w-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
        </div>
    </div>

    <div class="border-b border-zinc-200 px-4 py-4 dark:border-zinc-800 md:px-6">
        <div class="h-10 w-full animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
    </div>

    <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
        @for ($index = 0; $index < 3; $index++)
            <div class="grid grid-cols-1 gap-3 px-4 py-4 md:grid-cols-4 md:gap-6 md:px-6">
                <div class="h-5 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
                <div class="h-5 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
                <div class="h-5 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
                <div class="h-5 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
            </div>
        @endfor
    </div>
</div>
