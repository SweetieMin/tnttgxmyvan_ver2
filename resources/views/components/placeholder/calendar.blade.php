<div class="space-y-4">
    <flux:card class="overflow-hidden rounded-3xl border border-zinc-200 bg-white p-2 shadow-sm ring-1 ring-black/5 dark:border-zinc-700 dark:bg-zinc-900 dark:ring-white/10">

        {{-- Header skeleton --}}
        <div class="p-2 pb-4">
            <div class="rounded-2xl bg-zinc-50 p-4 dark:bg-zinc-800/50">
                <flux:skeleton.group animate="pulse">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:skeleton class="h-8 w-32 rounded-lg" />
                            <flux:skeleton class="h-8 w-28 rounded-lg" />
                            <flux:skeleton class="h-8 w-28 rounded-lg" />
                        </div>

                        <div class="flex items-center gap-3">
                            <flux:skeleton class="h-10 w-10 rounded-2xl" />
                            <div class="space-y-1.5">
                                <flux:skeleton class="h-4 w-28 rounded" />
                                <flux:skeleton class="h-3 w-44 rounded" />
                            </div>
                        </div>
                    </div>
                </flux:skeleton.group>
            </div>
        </div>

        {{-- Calendar grid skeleton --}}
        <div class="overflow-hidden rounded-[1.35rem] bg-zinc-100 dark:bg-zinc-800/60">
            <flux:skeleton.group animate="pulse">
                <div class="flex flex-col space-y-px">

                    {{-- Day-of-week header --}}
                    <div class="flex w-full flex-row gap-px">
                        @for ($i = 0; $i < 7; $i++)
                            <div class="flex h-10 flex-1 items-center justify-center">
                                <flux:skeleton class="h-3 w-6 rounded" />
                            </div>
                        @endfor
                    </div>

                    {{-- 5 weeks of day cells --}}
                    @for ($week = 0; $week < 5; $week++)
                        <div class="flex w-full flex-row gap-px">
                            @for ($day = 0; $day < 7; $day++)
                                <div class="flex h-35 flex-1 flex-col bg-white p-2 dark:bg-zinc-900 lg:h-47 lg:p-3" style="min-width: 5.9rem;">
                                    <div class="flex items-center gap-2">
                                        <flux:skeleton class="h-7 w-7 rounded-full lg:h-8 lg:w-8" />
                                    </div>

                                    @if ($week === 1 && in_array($day, [1, 4]))
                                        <div class="mt-2 lg:mt-3">
                                            <flux:skeleton class="h-10 w-full rounded-xl lg:h-12 lg:rounded-2xl" />
                                        </div>
                                    @endif

                                    @if ($week === 3 && in_array($day, [0, 3, 5]))
                                        <div class="mt-2 lg:mt-3">
                                            <flux:skeleton class="h-10 w-full rounded-xl lg:h-12 lg:rounded-2xl" />
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    @endfor

                </div>
            </flux:skeleton.group>
        </div>
    </flux:card>
</div>
