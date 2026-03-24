<div>
    <div class="hidden md:block">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-950/20">
            <div class="space-y-3">
                <div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                <div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                <div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
            </div>
        </div>
    </div>

    <div class="space-y-3 md:hidden">
        @for ($index = 0; $index < 3; $index++)
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950/20">
                <div class="space-y-3">
                    <div class="h-5 w-1/2 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
                    <div class="h-4 w-full animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
                    <div class="h-4 w-5/6 animate-pulse rounded-lg bg-zinc-100 dark:bg-zinc-800"></div>
                    <div class="flex gap-2 pt-1">
                        <div class="h-8 w-20 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                        <div class="h-8 w-20 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
    
</div>
