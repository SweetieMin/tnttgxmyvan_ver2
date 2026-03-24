@props([
    'categorySummaries' => [],
])

<flux:card class="overflow-hidden rounded-2xl p-6">
    <div class="mb-4 flex flex-col gap-2">
        <flux:heading size="lg">{{ __('Detailed category breakdown') }}</flux:heading>
        <flux:text>{{ __('Review transactions, income, expense, and balance for each category.') }}</flux:text>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Category') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Transactions') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Total income') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Total expense') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Balance') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($categorySummaries as $summary)
                <flux:table.row :key="$summary['id']" wire:key="category-summary-{{ $summary['id'] }}">
                    <flux:table.cell variant="strong">{{ $summary['name'] }}</flux:table.cell>
                    <flux:table.cell align="end">{{ number_format($summary['transactions_count'], 0, ',', '.') }}</flux:table.cell>
                    <flux:table.cell align="end">{{ number_format($summary['total_income'], 0, ',', '.') }} đ</flux:table.cell>
                    <flux:table.cell align="end">{{ number_format($summary['total_expense'], 0, ',', '.') }} đ</flux:table.cell>
                    <flux:table.cell align="end">
                        <span class="{{ $summary['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ number_format($summary['balance'], 0, ',', '.') }} đ
                        </span>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-8 text-center">
                        {{ __('No analytics data available for the selected filters.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
