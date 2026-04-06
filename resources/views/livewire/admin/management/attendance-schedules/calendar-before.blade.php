<flux:card class="border-0 bg-(--color-background) p-4">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <flux:button size="sm" variant="ghost" icon="chevron-left" wire:click="goToPreviousMonth">
                {{ __('Previous month') }}
            </flux:button>
            <flux:button size="sm" variant="primary" wire:click="goToCurrentMonth">
                {{ __('Current month') }}
            </flux:button>
            <flux:button size="sm" variant="ghost" icon-trailing="chevron-right" wire:click="goToNextMonth">
                {{ __('Next month') }}
            </flux:button>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-(--color-background-icon) text-(--color-accent-content)">
                <flux:icon name="calendar-days" class="h-5 w-5" />
            </div>

            <div class="space-y-0.5">
                <flux:heading size="sm" class="text-(--color-accent-content)">
                    {{ $this->startsAt->translatedFormat('F Y') }}
                </flux:heading>
                <flux:text class="text-xs text-(--color-accent-content)/80">
                    {{ __('Attendance schedule overview by month') }}
                </flux:text>
            </div>
        </div>
    </div>
</flux:card>
