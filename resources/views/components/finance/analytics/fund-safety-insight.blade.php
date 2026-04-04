@props([
    'fundSafetyInsight' => [
        'title' => '',
        'description' => '',
        'current_balance' => 0,
        'average_annual_expense' => 0,
        'highest_annual_expense' => 0,
        'recommended_reserve' => 0,
        'safe_to_spend' => 0,
        'years_used' => [],
        'status' => 'watch',
    ],
])

@php
    $statusColor = match ($fundSafetyInsight['status']) {
        'safe' => 'success',
        'risk' => 'danger',
        default => 'warning',
    };

    $statusLabel = match ($fundSafetyInsight['status']) {
        'safe' => __('Reserve looks safe'),
        'risk' => __('Reserve is below a safe level'),
        default => __('Reserve should be monitored'),
    };
@endphp

<flux:card class="rounded-2xl p-6">
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col gap-2">
            <flux:heading size="lg">{{ $fundSafetyInsight['title'] }}</flux:heading>
            <flux:text>{{ $fundSafetyInsight['description'] }}</flux:text>
        </div>

        <flux:badge :color="$statusColor">{{ $statusLabel }}</flux:badge>
    </div>

    <div class="mt-4 grid gap-3 md:grid-cols-2">
        <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/70">
            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Current recorded balance') }}</div>
            <div class="mt-1 text-xl font-semibold text-zinc-900 dark:text-white">
                {{ number_format($fundSafetyInsight['current_balance'], 0, ',', '.') }} đ
            </div>
        </div>

        <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900/70">
            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Safe to spend now') }}</div>
            <div class="mt-1 text-xl font-semibold text-zinc-900 dark:text-white">
                {{ number_format($fundSafetyInsight['safe_to_spend'], 0, ',', '.') }} đ
            </div>
        </div>
    </div>

    <div class="mt-4 space-y-3">
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Average annual expense') }}</span>
            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($fundSafetyInsight['average_annual_expense'], 0, ',', '.') }} đ</span>
        </div>
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Highest annual expense') }}</span>
            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($fundSafetyInsight['highest_annual_expense'], 0, ',', '.') }} đ</span>
        </div>
        <div class="flex items-center justify-between gap-3 text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Recommended reserve') }}</span>
            <span class="font-medium text-zinc-900 dark:text-white">{{ number_format($fundSafetyInsight['recommended_reserve'], 0, ',', '.') }} đ</span>
        </div>
    </div>

    <div class="mt-4 rounded-xl border border-dashed border-zinc-200 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
        @if ($fundSafetyInsight['years_used'] !== [])
            {{ __('Based on recorded expense history from :years.', ['years' => implode(', ', $fundSafetyInsight['years_used'])]) }}
        @else
            {{ __('No multi-year expense history is available yet. The reserve estimate will improve after more yearly data is recorded.') }}
        @endif
    </div>
</flux:card>
