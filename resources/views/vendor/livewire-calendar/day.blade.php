
<div
    ondragenter="onLivewireCalendarEventDragEnter(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragleave="onLivewireCalendarEventDragLeave(event, '{{ $componentId }}', '{{ $day }}', '{{ $dragAndDropClasses }}');"
    ondragover="onLivewireCalendarEventDragOver(event);"
    ondrop="onLivewireCalendarEventDrop(event, '{{ $componentId }}', '{{ $day }}', {{ $day->year }}, {{ $day->month }}, {{ $day->day }}, '{{ $dragAndDropClasses }}');"
    class="flex h-35 flex-1 lg:h-47"
    style="min-width: 5.9rem;"
>
    <div
        class="h-full w-full"
        id="{{ $componentId }}-{{ $day }}"
    >

        <div
            @if($dayClickEnabled)
                wire:click="onDayClick({{ $day->year }}, {{ $day->month }}, {{ $day->day }})"
            @endif
            class="flex h-full w-full flex-col p-2 transition-colors lg:p-3 {{ $dayClickEnabled ? 'cursor-pointer' : '' }} {{ $dayInMonth ? ($isToday ? 'bg-(--color-background)/45 dark:bg-zinc-800/90' : 'bg-white/95 hover:bg-(--color-background)/18 dark:bg-zinc-900/90 dark:hover:bg-zinc-800/90') : 'bg-zinc-50/95 dark:bg-zinc-950/50' }}"
        >

            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full px-1.5 text-xs font-semibold lg:h-8 lg:min-w-8 lg:px-2 lg:text-sm {{ $dayInMonth ? ($isToday ? 'bg-(--color-accent) text-white shadow-sm shadow-sky-200/60 dark:shadow-none' : 'bg-(--color-background) text-(--color-accent-content) dark:bg-zinc-800 dark:text-zinc-100') : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-500' }}">
                        {{ $day->format('j') }}
                    </span>
                    @if($events->isNotEmpty())
                    <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-(--color-background) px-1.5 py-0.5 text-[10px] font-semibold text-(--color-accent-content) dark:bg-zinc-800 dark:text-zinc-100 lg:min-w-7 lg:px-2 lg:text-[11px]">
                        {{ $events->count() }} {{ __('Event') }}
                    </span>
                @endif
                </div>

                <div class="flex items-center gap-1.5">
                    @if($isToday)
                        <span class="inline-flex h-2.5 w-2.5 rounded-full bg-(--color-accent) ring-2 ring-white/80 dark:ring-zinc-900/90"></span>
                    @endif


                </div>
            </div>

            <div class="mt-2 flex-1 overflow-y-auto pr-0.5 lg:mt-3 lg:pr-1">
                <div class="grid grid-cols-1 grid-flow-row gap-2">
                    @foreach($events as $event)
                        <div
                            @if($dragAndDropEnabled)
                                draggable="true"
                            @endif
                            ondragstart="onLivewireCalendarEventDragStart(event, '{{ $event['id'] }}')"
                            class="{{ $dragAndDropEnabled ? 'cursor-grab active:cursor-grabbing' : '' }}"
                        >
                            @include($eventView, [
                                'event' => $event,
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
