<div
    @if($eventClickEnabled)
        wire:click.stop="onEventClick('{{ $event['id']  }}')"
    @endif
    class="{{ $eventClickEnabled ? 'cursor-pointer' : '' }}"
>
    <div class="rounded-xl border p-1.5 shadow-xs shadow-zinc-200/30 transition dark:shadow-none lg:rounded-2xl lg:p-2.5 {{ $event['border_class'] ?? 'border-zinc-200/70 dark:border-zinc-700/70' }} {{ $event['background_class'] ?? 'bg-white/95 dark:bg-zinc-900/95' }} {{ $event['hover_class'] ?? 'hover:border-(--color-accent) hover:shadow-sm hover:shadow-sky-100/40 dark:hover:bg-zinc-800/95' }}">
        <div class="flex items-start gap-1.5 lg:gap-2.5">
            <span class="mt-0.5 h-6 w-1 shrink-0 rounded-full lg:h-8 lg:w-1.5 {{ $event['dot_class'] ?? 'bg-(--color-accent)' }}"></span>

            <div class="min-w-0 flex-1 space-y-1">
                <flux:text class="truncate text-[9px] leading-3 font-semibold text-zinc-900 dark:text-white lg:hidden">
                    {{ $event['mobile_label'] ?? $event['title'] }}
                </flux:text>

                <div class="hidden lg:block">
                <flux:text class="truncate text-[10px] font-semibold text-zinc-900 dark:text-white lg:text-xs">
                    {{ $event['title'] }}
                </flux:text>

                @if(filled($event['description'] ?? null))
                    <flux:text class="hidden truncate text-[11px] leading-4 text-zinc-500 dark:text-zinc-400 lg:block">
                        {{ $event['description'] }}
                    </flux:text>
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
