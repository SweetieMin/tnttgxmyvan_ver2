@props([
    'overviewCards' => [],
])

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($overviewCards as $card)
        <flux:card wire:key="analytics-card-{{ $card['label'] }}" class="rounded-2xl p-5">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-2">
                    <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $card['label'] }}</div>
                    <div class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ $card['value'] }}</div>
                </div>

                <flux:badge :color="$card['tone']">{{ __('Overview') }}</flux:badge>
            </div>
        </flux:card>
    @endforeach
</div>
