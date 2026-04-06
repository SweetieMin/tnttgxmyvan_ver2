<div
    @if($pollMillis !== null && $pollAction !== null)
        wire:poll.{{ $pollMillis }}ms="{{ $pollAction }}"
    @elseif($pollMillis !== null)
        wire:poll.{{ $pollMillis }}ms
    @endif
    class="space-y-4"
>
    <flux:card class="overflow-hidden rounded-3xl border border-(--color-background-icon) bg-[color:color-mix(in_srgb,var(--color-background)_14%,white)] p-2 shadow-sm shadow-[color:color-mix(in_srgb,var(--color-heading-table)_16%,transparent)] ring-1 ring-black/5 dark:border-[color:color-mix(in_srgb,var(--color-heading-table)_24%,var(--color-zinc-700))] dark:bg-[color:color-mix(in_srgb,var(--color-zinc-900)_84%,var(--color-heading-table)_16%)] dark:shadow-none dark:ring-white/10">
        @if($beforeCalendarView)
            <div class="p-2 pb-4">
                @include($beforeCalendarView)
            </div>
        @endif

        <div class="overflow-hidden rounded-[1.35rem] bg-[color:color-mix(in_srgb,var(--color-background-icon)_55%,white)] dark:bg-[color:color-mix(in_srgb,var(--color-heading-table)_14%,var(--color-zinc-900))]">
            <div class="flex">
                <div class="w-full overflow-x-auto">
                    <div class="inline-block min-w-full overflow-hidden space-y-px">

                        <div class="flex w-full flex-row gap-px">
                        @foreach($monthGrid->first() as $day)
                            @include($dayOfWeekView, ['day' => $day])
                        @endforeach
                        </div>

                        @foreach($monthGrid as $week)
                            <div class="flex w-full flex-row gap-px">
                                @foreach($week as $day)
                                    @include($dayView, [
                                            'componentId' => $componentId,
                                            'day' => $day,
                                            'dayInMonth' => $day->isSameMonth($startsAt),
                                            'isToday' => $day->isToday(),
                                            'events' => $getEventsForDay($day, $events),
                                        ])
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if($afterCalendarView)
            <div class="p-2 pt-4">
                @include($afterCalendarView)
            </div>
        @endif
    </flux:card>
</div>
