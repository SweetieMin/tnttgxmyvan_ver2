<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Category analytics')"
        :sub-title="__('Review income and expense performance across categories with charts and summaries.')"
        icon="presentation-chart-line"
    />

    <x-app-filter :show-search="false" :categories="$categoryOptions" :date-range="true" :reset-action="'resetFilters'" />

    <x-finance.analytics.overview-cards :overview-cards="$overviewCards" />

    <div class="grid gap-4 xl:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <div class="min-w-0">
            @island(name: 'category-bar-chart', always: true, lazy: true)
                @placeholder
                    <flux:card class="rounded-2xl p-6">
                        <div class="space-y-3">
                            <flux:skeleton class="h-6 w-52" />
                            <flux:skeleton class="h-4 w-80 max-w-full" />
                            <div class="mt-4 flex gap-3">
                                <flux:skeleton class="h-4 w-18" />
                                <flux:skeleton class="h-4 w-18" />
                            </div>
                            <flux:skeleton class="mt-4 h-80 w-full rounded-2xl" />
                        </div>
                    </flux:card>
                @endplaceholder
                <x-finance.analytics.category-bar-chart :comparison-chart="$this->categoryComparisonChart()" />
            @endisland
        </div>

        <div class="min-w-0">
            @island(name: 'time-trend-chart', always: true, lazy: true)
                @placeholder
                    <flux:card class="rounded-2xl p-6">
                        <div class="space-y-3">
                            <flux:skeleton class="h-6 w-44" />
                            <flux:skeleton class="h-4 w-80 max-w-full" />
                            <div class="mt-4 flex gap-3">
                                <flux:skeleton class="h-4 w-18" />
                                <flux:skeleton class="h-4 w-18" />
                            </div>
                            <flux:skeleton class="mt-4 h-80 w-full rounded-2xl" />
                        </div>
                    </flux:card>
                @endplaceholder
                <x-finance.analytics.time-trend-chart :time-trend-chart="$this->timeTrendChart()" />
            @endisland
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[minmax(0,5fr)_minmax(0,7fr)]">
        <div class="min-w-0">
            @island(name: 'expense-share-breakdown', always: true, lazy: true)
                @placeholder
                    <flux:card class="rounded-2xl p-6">
                        <div class="space-y-4">
                            <flux:skeleton class="h-6 w-48" />
                            <flux:skeleton class="h-4 w-72 max-w-full" />
                            @foreach (range(1, 4) as $placeholderIndex)
                                <div class="space-y-2" wire:key="expense-share-placeholder-{{ $placeholderIndex }}">
                                    <div class="flex items-center justify-between gap-3">
                                        <flux:skeleton class="h-4 w-28" />
                                        <flux:skeleton class="h-4 w-12" />
                                    </div>
                                    <flux:skeleton class="h-2 w-full rounded-full" />
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @endplaceholder
                <x-finance.analytics.expense-share-breakdown :expense-share-breakdown="$this->expenseShareBreakdown($this->categorySummaries())" />
            @endisland
        </div>

        <div class="min-w-0">
            @island(name: 'category-summary-table', always: true, lazy: true)
                @placeholder
                    <flux:card class="overflow-hidden rounded-2xl p-6">
                        <div class="mb-4 flex flex-col gap-2">
                            <flux:heading size="lg">{{ __('Detailed category breakdown') }}</flux:heading>
                            <flux:text>{{ __('Review transactions, income, expense, and balance for each category.') }}</flux:text>
                        </div>
                        <x-placeholder.table />
                    </flux:card>
                @endplaceholder
                <x-finance.analytics.category-summary-table :category-summaries="$this->categorySummaries()" />
            @endisland
        </div>
    </div>
</section>
