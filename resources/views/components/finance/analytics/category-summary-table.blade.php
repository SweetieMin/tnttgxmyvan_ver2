@props([
    'categorySummaries' => [],
])

<x-app-layout-table>
    <x-slot:desktop>
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
    </x-slot:desktop>

    <x-slot:mobile>
        <flux:card class="rounded-2xl p-4">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Detailed category breakdown') }}</flux:heading>
                <flux:text>{{ __('Review transactions, income, expense, and balance for each category.') }}</flux:text>
            </div>

            @if (count($categorySummaries) > 0)
                <div class="mt-4">
                    <flux:accordion variant="reverse">
                        @foreach ($categorySummaries as $summary)
                            <flux:accordion.item wire:key="category-summary-mobile-{{ $summary['id'] }}">
                                <flux:accordion.heading class="px-0 py-3">
                                    <div class="flex w-full items-start justify-between gap-3 text-left">
                                        <div class="space-y-1">
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $summary['name'] }}</p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ number_format($summary['transactions_count'], 0, ',', '.') }} {{ __('Transactions') }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Balance') }}</p>
                                            <p class="text-sm font-semibold {{ $summary['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                {{ number_format($summary['balance'], 0, ',', '.') }} đ
                                            </p>
                                        </div>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content class="px-0 pb-3">
                                    <div class="grid grid-cols-2 gap-3 rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-900/70">
                                        <div>
                                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Total income') }}</div>
                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($summary['total_income'], 0, ',', '.') }} đ
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-zinc-500 dark:text-zinc-400">{{ __('Total expense') }}</div>
                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                {{ number_format($summary['total_expense'], 0, ',', '.') }} đ
                                            </div>
                                        </div>
                                    </div>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        @endforeach
                    </flux:accordion>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    {{ __('No analytics data available for the selected filters.') }}
                </div>
            @endif
        </flux:card>
    </x-slot:mobile>
</x-app-layout-table>
