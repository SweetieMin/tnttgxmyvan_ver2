@props([
    'expenseShareBreakdown' => collect(),
])

<flux:card class="rounded-2xl p-6">
    <div class="flex flex-col gap-2">
        <flux:heading size="lg">{{ __('Expense share by category') }}</flux:heading>
        <flux:text>{{ __('See which categories account for the largest share of expenses.') }}</flux:text>
    </div>

    @if ($expenseShareBreakdown->isNotEmpty())
        <div class="mt-5 space-y-4">
            @foreach ($expenseShareBreakdown as $item)
                <div wire:key="expense-share-{{ $item['name'] }}" class="space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $item['name'] }}: {{ number_format($item['amount'], 0, ',', '.') }} đ</div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ number_format($item['share_percentage'], 1, ',', '.') }}%
                        </div>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                        <div class="h-full rounded-full bg-rose-500 dark:bg-rose-400"
                            style="width: {{ min($item['share_percentage'], 100) }}%"></div>
                    </div>

                </div>
            @endforeach
        </div>
    @else
        <flux:callout class="mt-4" color="sky" icon="information-circle"
            :heading="__('No expense data available for the selected filters.')" />
    @endif
</flux:card>
