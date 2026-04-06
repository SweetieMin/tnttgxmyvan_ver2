@props([
    'yearOverYearInsight' => [
        'title' => '',
        'description' => '',
        'current_label' => '',
        'previous_label' => '',
        'income_current' => 0,
        'income_previous' => 0,
        'expense_current' => 0,
        'expense_previous' => 0,
        'income_delta' => 0,
        'expense_delta' => 0,
        'income_delta_percentage' => 0.0,
        'expense_delta_percentage' => 0.0,
    ],
])

@php
    $rows = [
        [
            'label' => __('Income'),
            'current' => $yearOverYearInsight['income_current'],
            'previous' => $yearOverYearInsight['income_previous'],
            'delta' => $yearOverYearInsight['income_delta'],
            'percentage' => $yearOverYearInsight['income_delta_percentage'],
            'tone' => 'emerald',
        ],
        [
            'label' => __('Expense'),
            'current' => $yearOverYearInsight['expense_current'],
            'previous' => $yearOverYearInsight['expense_previous'],
            'delta' => $yearOverYearInsight['expense_delta'],
            'percentage' => $yearOverYearInsight['expense_delta_percentage'],
            'tone' => 'rose',
        ],
    ];
@endphp

<flux:card class="rounded-2xl p-6">
    <div class="flex flex-col gap-2">
        <flux:heading size="lg">{{ $yearOverYearInsight['title'] }}</flux:heading>
        <flux:text>{{ $yearOverYearInsight['description'] }}</flux:text>
    </div>

    <div class="mt-4 grid gap-3 md:grid-cols-2">
        <div class="rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-900/70">
            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Current period') }}</div>
            <div class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $yearOverYearInsight['current_label'] }}</div>
        </div>
        <div class="rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-900/70">
            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Same period last year') }}</div>
            <div class="mt-1 font-medium text-zinc-900 dark:text-white">{{ $yearOverYearInsight['previous_label'] }}</div>
        </div>
    </div>

    <div class="mt-4 space-y-3">
        @foreach ($rows as $row)
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800" wire:key="year-over-year-row-{{ $row['label'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $row['label'] }}</div>
                        <div class="mt-1 text-xl font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($row['current'], 0, ',', '.') }} đ
                        </div>
                    </div>

                    <flux:badge :color="$row['tone']">
                        {{ $row['delta'] >= 0 ? '+' : '' }}{{ number_format($row['delta'], 0, ',', '.') }} đ
                    </flux:badge>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm">
                    <div class="text-zinc-500 dark:text-zinc-400">
                        {{ __('Last year') }}: {{ number_format($row['previous'], 0, ',', '.') }} đ
                    </div>

                    <div class="font-medium {{ $row['delta'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        @if ($row['percentage'] === null)
                            {{ __('No baseline last year') }}
                        @else
                            {{ $row['delta'] >= 0 ? '+' : '' }}{{ number_format($row['percentage'], 1, ',', '.') }}%
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</flux:card>
