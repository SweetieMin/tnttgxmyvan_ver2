<?php

namespace App\Livewire\Admin\Finance\Categories;

use App\Models\Category;
use App\Models\Transaction;
use Flux\DateRange;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Phân tích hạng mục')]
class CategoryAnalytics extends Component
{
    public string $selectedCategory = '';

    public ?DateRange $dateRange = null;

    /**
     * @var Collection<int, array{value: string, label: string}>|null
     */
    protected ?Collection $resolvedCategoryOptions = null;

    /**
     * @var array<string, Collection<int, array{
     *     id: int,
     *     name: string,
     *     transactions_count: int,
     *     total_income: int,
     *     total_expense: int,
     *     balance: int
     * }>>
     */
    protected array $resolvedCategorySummaries = [];

    /**
     * @var array<string, array<int, array{date: string, income: int, expense: int}>>
     */
    protected array $resolvedTimeTrendCharts = [];

    /**
     * @var array<string, array{
     *     title: string,
     *     description: string,
     *     points: array<int, array{category: string, income: int, expense: int}>
     * }>
     */
    protected array $resolvedComparisonCharts = [];

    /**
     * @var array<string, array{
     *     title: string,
     *     description: string,
     *     current_label: string,
     *     previous_label: string,
     *     income_current: int,
     *     income_previous: int,
     *     expense_current: int,
     *     expense_previous: int,
     *     income_delta: int,
     *     expense_delta: int,
     *     income_delta_percentage: float|null,
     *     expense_delta_percentage: float|null
     * }>
     */
    protected array $resolvedYearOverYearInsights = [];

    /**
     * @var array<string, array{
     *     title: string,
     *     description: string,
     *     current_balance: int,
     *     average_annual_expense: int,
     *     highest_annual_expense: int,
     *     recommended_reserve: int,
     *     safe_to_spend: int,
     *     years_used: array<int, string>,
     *     status: string
     * }>
     */
    protected array $resolvedFundSafetyInsights = [];

    public function mount(): void
    {
        $this->dateRange = DateRange::yearToDate();
    }

    public function applyFilters(): void {}

    public function resetFilters(): void
    {
        $this->selectedCategory = '';
        $this->dateRange = null;
    }

    public function updatedDateRange(): void
    {
        if ($this->dateRange !== null && ! $this->dateRange->hasStart() && ! $this->dateRange->hasEnd()) {
            $this->dateRange = null;
        }
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    public function categoryOptions(): Collection
    {
        if ($this->resolvedCategoryOptions !== null) {
            return $this->resolvedCategoryOptions;
        }

        $this->resolvedCategoryOptions = Category::query()
            ->where('is_active', true)
            ->orderBy('ordering')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'value' => (string) $category->id,
                'label' => $category->name,
            ]);

        return $this->resolvedCategoryOptions;
    }

    public function render(): View
    {
        $categoryOptions = $this->categoryOptions();
        $overviewCards = $this->overviewCards();

        return view('livewire.admin.finance.categories.category-analytics', [
            'categoryOptions' => $categoryOptions,
            'overviewCards' => $overviewCards,
            'appliedCategoriesLabel' => $this->appliedCategoriesLabel(),
            'appliedDateRangeLabel' => $this->appliedDateRangeLabel(),
            'yearOverYearInsight' => $this->yearOverYearInsight(),
            'fundSafetyInsight' => $this->fundSafetyInsight(),
        ]);
    }

    /**
     * @return Collection<int, array{
     *     id: int,
     *     name: string,
     *     transactions_count: int,
     *     total_income: int,
     *     total_expense: int,
     *     balance: int
     * }>
     */
    public function categorySummaries(): Collection
    {
        $cacheKey = $this->categoryAnalyticsCacheKey('summaries');

        if (array_key_exists($cacheKey, $this->resolvedCategorySummaries)) {
            return $this->resolvedCategorySummaries[$cacheKey];
        }

        /** @var Collection<int, array{
         *     id: int,
         *     name: string,
         *     transactions_count: int,
         *     total_income: int,
         *     total_expense: int,
         *     balance: int
         * }> $categorySummaries
         */
        $categorySummaries = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
            function (): Collection {
                return Category::query()
                    ->where('is_active', true)
                    ->when(
                        $this->selectedCategory !== '',
                        fn (Builder $query) => $query->whereKey($this->selectedCategory),
                    )
                    ->orderBy('ordering')
                    ->orderBy('name')
                    ->withCount([
                        'transactions as transactions_count' => fn (Builder $query): Builder => $this->applyDateRangeFilter($query, $this->dateRange),
                    ])
                    ->withSum([
                        'transactions as total_income' => fn (Builder $query): Builder => $this->applyDateRangeFilter($query, $this->dateRange)
                            ->where('type', 'income'),
                    ], 'amount')
                    ->withSum([
                        'transactions as total_expense' => fn (Builder $query): Builder => $this->applyDateRangeFilter($query, $this->dateRange)
                            ->where('type', 'expense'),
                    ], 'amount')
                    ->get()
                    ->map(function (Category $category): array {
                        $totalIncome = (int) ($category->getAttribute('total_income') ?? 0);
                        $totalExpense = (int) ($category->getAttribute('total_expense') ?? 0);

                        return [
                            'id' => (int) $category->id,
                            'name' => $category->name,
                            'transactions_count' => (int) ($category->getAttribute('transactions_count') ?? 0),
                            'total_income' => $totalIncome,
                            'total_expense' => $totalExpense,
                            'balance' => $totalIncome - $totalExpense,
                        ];
                    })
                    ->sortByDesc('balance')
                    ->values();
            },
        );

        $this->resolvedCategorySummaries[$cacheKey] = $categorySummaries;

        return $categorySummaries;
    }

    /**
     * @return array<int, array{label: string, value: string, tone: string}>
     */
    protected function overviewCards(): array
    {
        $totals = $this->transactionQuery()
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense")
            ->first();

        $totalIncome = (int) ($totals?->total_income ?? 0);
        $totalExpense = (int) ($totals?->total_expense ?? 0);
        $balance = $totalIncome - $totalExpense;
        $totalTransactions = (int) ($totals?->total_transactions ?? 0);

        return [
            [
                'label' => __('Total transactions'),
                'value' => number_format($totalTransactions, 0, ',', '.'),
                'tone' => 'sky',
            ],
            [
                'label' => __('Total income'),
                'value' => $this->formatMoney($totalIncome),
                'tone' => 'emerald',
            ],
            [
                'label' => __('Total expense'),
                'value' => $this->formatMoney($totalExpense),
                'tone' => 'rose',
            ],
            [
                'label' => __('Remaining balance'),
                'value' => $this->formatMoney($balance),
                'tone' => $balance >= 0 ? 'amber' : 'rose',
            ],
        ];
    }

    public function yearOverYearInsight(): array
    {
        $cacheKey = $this->categoryAnalyticsCacheKey('year-over-year');

        if (array_key_exists($cacheKey, $this->resolvedYearOverYearInsights)) {
            return $this->resolvedYearOverYearInsights[$cacheKey];
        }

        [$currentStart, $currentEnd, $currentLabel, $previousStart, $previousEnd, $previousLabel] = $this->comparisonPeriods();

        $currentMetrics = $this->metricsForPeriod($currentStart, $currentEnd);
        $previousMetrics = $this->metricsForPeriod($previousStart, $previousEnd);
        $scopeLabel = $this->appliedCategoriesLabel();

        $this->resolvedYearOverYearInsights[$cacheKey] = [
            'title' => __('Compared with last year'),
            'description' => __('Review how :scope is performing against the same period last year.', [
                'scope' => $scopeLabel,
            ]),
            'current_label' => $currentLabel,
            'previous_label' => $previousLabel,
            'income_current' => $currentMetrics['income'],
            'income_previous' => $previousMetrics['income'],
            'expense_current' => $currentMetrics['expense'],
            'expense_previous' => $previousMetrics['expense'],
            'income_delta' => $currentMetrics['income'] - $previousMetrics['income'],
            'expense_delta' => $currentMetrics['expense'] - $previousMetrics['expense'],
            'income_delta_percentage' => $this->deltaPercentage($currentMetrics['income'], $previousMetrics['income']),
            'expense_delta_percentage' => $this->deltaPercentage($currentMetrics['expense'], $previousMetrics['expense']),
        ];

        return $this->resolvedYearOverYearInsights[$cacheKey];
    }

    public function fundSafetyInsight(): array
    {
        $categoryId = $this->selectedCategory !== '' ? $this->selectedCategory : 'all';
        $cacheKey = "finance.category-analytics.v3.fund-safety.{$categoryId}";

        if (array_key_exists($cacheKey, $this->resolvedFundSafetyInsights)) {
            return $this->resolvedFundSafetyInsights[$cacheKey];
        }

        /** @var array<int, array{year: string, expense: int}> $annualExpenseHistory */
        $annualExpenseHistory = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
            function (): array {
                $yearExpression = $this->yearExpression('transaction_date');

                return $this->transactionBaseQuery()
                    ->where('type', 'expense')
                    ->selectRaw("{$yearExpression} as summary_year")
                    ->selectRaw('COALESCE(SUM(amount), 0) as total_expense')
                    ->groupBy('summary_year')
                    ->orderByDesc('summary_year')
                    ->get()
                    ->map(fn (Transaction $transaction): array => [
                        'year' => (string) $transaction->getAttribute('summary_year'),
                        'expense' => (int) ($transaction->getAttribute('total_expense') ?? 0),
                    ])
                    ->all();
            },
        );

        $currentYear = (string) Carbon::now()->year;
        $recentExpenses = collect($annualExpenseHistory)
            ->reject(fn (array $item): bool => $item['year'] === $currentYear)
            ->take(3);

        if ($recentExpenses->isEmpty()) {
            $recentExpenses = collect($annualExpenseHistory)->take(3);
        }

        $averageAnnualExpense = (int) round($recentExpenses->avg('expense') ?? 0);
        $highestAnnualExpense = (int) $recentExpenses->max('expense');
        $recommendedReserve = max(
            (int) round($averageAnnualExpense * 1.1),
            (int) round($highestAnnualExpense * 0.85),
        );

        $totals = $this->transactionBaseQuery()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense")
            ->first();

        $currentBalance = (int) ($totals?->total_income ?? 0) - (int) ($totals?->total_expense ?? 0);
        $safeToSpend = max($currentBalance - $recommendedReserve, 0);
        $status = $currentBalance >= $recommendedReserve
            ? 'safe'
            : ($currentBalance > 0 ? 'watch' : 'risk');

        $scopeLabel = $this->appliedCategoriesLabel();

        $this->resolvedFundSafetyInsights[$cacheKey] = [
            'title' => __('Fund safety'),
            'description' => __('Use the most recent recorded years of :scope to estimate a safer reserve before planning new spending.', [
                'scope' => $scopeLabel,
            ]),
            'current_balance' => $currentBalance,
            'average_annual_expense' => $averageAnnualExpense,
            'highest_annual_expense' => $highestAnnualExpense,
            'recommended_reserve' => $recommendedReserve,
            'safe_to_spend' => $safeToSpend,
            'years_used' => $recentExpenses
                ->pluck('year')
                ->reverse()
                ->values()
                ->all(),
            'status' => $status,
        ];

        return $this->resolvedFundSafetyInsights[$cacheKey];
    }

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     points: array<int, array{category: string, income: int, expense: int}>
     * }
     */
    public function categoryComparisonChart(): array
    {
        $cacheKey = $this->categoryAnalyticsCacheKey('comparison-chart');

        if (array_key_exists($cacheKey, $this->resolvedComparisonCharts)) {
            return $this->resolvedComparisonCharts[$cacheKey];
        }

        if ($this->selectedCategory !== '') {
            $selectedCategoryName = Category::query()->whereKey($this->selectedCategory)->value('name');

            /** @var array<int, array{category: string, income: int, expense: int}> $points */
            $points = Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                function (): array {
                    $yearExpression = $this->yearExpression('transaction_date');

                    return $this->transactionQuery()
                        ->selectRaw("{$yearExpression} as summary_year")
                        ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income")
                        ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense")
                        ->groupBy('summary_year')
                        ->orderBy('summary_year')
                        ->get()
                        ->map(fn (Transaction $transaction): array => [
                            'category' => (string) $transaction->getAttribute('summary_year'),
                            'income' => (int) ($transaction->getAttribute('total_income') ?? 0),
                            'expense' => (int) ($transaction->getAttribute('total_expense') ?? 0),
                        ])
                        ->all();
                },
            );

            $this->resolvedComparisonCharts[$cacheKey] = [
                'title' => __('Income vs expense by year'),
                'description' => $this->dateRange !== null && ($this->dateRange->hasStart() || $this->dateRange->hasEnd())
                    ? __('Review yearly income and expense totals for :category within the selected date range.', [
                        'category' => $selectedCategoryName ?? __('the selected category'),
                    ])
                    : __('Review yearly income and expense totals for :category across all recorded years.', [
                        'category' => $selectedCategoryName ?? __('the selected category'),
                    ]),
                'points' => $points,
            ];

            return $this->resolvedComparisonCharts[$cacheKey];
        }

        $categorySummaries = $this->categorySummaries();

        $this->resolvedComparisonCharts[$cacheKey] = [
            'title' => __('Income vs expense by category'),
            'description' => __('Compare how each category contributes to cash inflow and outflow.'),
            'points' => $categorySummaries
                ->filter(fn (array $summary): bool => $summary['total_income'] > 0 || $summary['total_expense'] > 0)
                ->map(fn (array $summary): array => [
                    'category' => $summary['name'],
                    'income' => $summary['total_income'],
                    'expense' => $summary['total_expense'],
                ])
                ->values()
                ->all(),
        ];

        return $this->resolvedComparisonCharts[$cacheKey];
    }

    /**
     * @param  Collection<int, array{
     *     id: int,
     *     name: string,
     *     transactions_count: int,
     *     total_income: int,
     *     total_expense: int,
     *     balance: int
     * }>  $categorySummaries
     * @return Collection<int, array{name: string, amount: int, share_percentage: float}>
     */
    public function expenseShareBreakdown(Collection $categorySummaries): Collection
    {
        $totalExpense = (int) $categorySummaries->sum('total_expense');

        return $categorySummaries
            ->filter(fn (array $summary): bool => $summary['total_expense'] > 0)
            ->sortByDesc('total_expense')
            ->values()
            ->map(fn (array $summary): array => [
                'name' => $summary['name'],
                'amount' => $summary['total_expense'],
                'share_percentage' => $totalExpense > 0
                    ? round(($summary['total_expense'] / $totalExpense) * 100, 1)
                    : 0.0,
            ]);
    }

    /**
     * @return array<int, array{date: string, income: int, expense: int}>
     */
    public function timeTrendChart(): array
    {
        $cacheKey = $this->categoryAnalyticsCacheKey('time-trend');

        if (array_key_exists($cacheKey, $this->resolvedTimeTrendCharts)) {
            return $this->resolvedTimeTrendCharts[$cacheKey];
        }

        /** @var array<int, array{date: string, income: int, expense: int}> $timeTrendChart */
        $timeTrendChart = Cache::remember(
            $cacheKey,
            now()->addMinutes(10),
            function (): array {
                return $this->transactionQuery()
                    ->selectRaw('DATE(transaction_date) as summary_date')
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income")
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense")
                    ->groupBy('summary_date')
                    ->orderBy('summary_date')
                    ->get()
                    ->map(function (Transaction $transaction): array {
                        $summaryDate = Carbon::parse($transaction->getAttribute('summary_date'));

                        return [
                            'date' => $summaryDate->format('d/m'),
                            'income' => (int) ($transaction->getAttribute('total_income') ?? 0),
                            'expense' => (int) ($transaction->getAttribute('total_expense') ?? 0),
                        ];
                    })
                    ->all();
            },
        );

        $this->resolvedTimeTrendCharts[$cacheKey] = $timeTrendChart;

        return $timeTrendChart;
    }

    protected function transactionQuery(): Builder
    {
        return $this->applyDateRangeFilter($this->transactionBaseQuery(), $this->dateRange);
    }

    protected function transactionBaseQuery(): Builder
    {
        return Transaction::query()
            ->when(
                $this->selectedCategory !== '',
                fn (Builder $query) => $query->where('category_id', $this->selectedCategory),
            );
    }

    protected function applyDateRangeFilter(Builder $query, ?DateRange $dateRange): Builder
    {
        return $query
            ->when(
                $dateRange?->hasStart(),
                fn (Builder $builder) => $builder->whereDate('transaction_date', '>=', $dateRange?->start()?->toDateString()),
            )
            ->when(
                $dateRange?->hasEnd(),
                fn (Builder $builder) => $builder->whereDate('transaction_date', '<=', $dateRange?->end()?->toDateString()),
            );
    }

    protected function appliedCategoriesLabel(): string
    {
        if ($this->selectedCategory === '') {
            return __('All active categories');
        }

        $categoryName = Category::query()->whereKey($this->selectedCategory)->value('name');

        return is_string($categoryName) && $categoryName !== ''
            ? $categoryName
            : __('All active categories');
    }

    protected function appliedDateRangeLabel(): string
    {
        if ($this->dateRange === null || (! $this->dateRange->hasStart() && ! $this->dateRange->hasEnd())) {
            return __('All time');
        }

        $start = $this->dateRange->start()?->format('d/m/Y');
        $end = $this->dateRange->end()?->format('d/m/Y');

        if ($start !== null && $end !== null) {
            return $start.' - '.$end;
        }

        return $start ?? $end ?? __('All time');
    }

    protected function formatMoney(int $amount): string
    {
        return number_format($amount, 0, ',', '.').' đ';
    }

    protected function categoryAnalyticsCacheKey(string $segment): string
    {
        $version = 'v2';
        $categoryId = $this->selectedCategory !== '' ? $this->selectedCategory : 'all';
        $start = $this->dateRange?->start()?->toDateString() ?? 'null';
        $end = $this->dateRange?->end()?->toDateString() ?? 'null';

        return "finance.category-analytics.{$version}.{$segment}.{$categoryId}.{$start}.{$end}";
    }

    protected function yearExpression(string $column): string
    {
        $driver = Transaction::query()->getQuery()->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return "strftime('%Y', {$column})";
        }

        return "YEAR({$column})";
    }

    /**
     * @return array{income: int, expense: int}
     */
    protected function metricsForPeriod(?Carbon $start, ?Carbon $end): array
    {
        $totals = $this->applyDateWindowFilter($this->transactionBaseQuery(), $start, $end)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense")
            ->first();

        return [
            'income' => (int) ($totals?->total_income ?? 0),
            'expense' => (int) ($totals?->total_expense ?? 0),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: Carbon, 4: Carbon, 5: string}
     */
    protected function comparisonPeriods(): array
    {
        $currentStart = $this->dateRange?->hasStart()
            ? Carbon::instance($this->dateRange->start())
            : Carbon::now()->startOfYear();
        $currentEnd = $this->dateRange?->hasEnd()
            ? Carbon::instance($this->dateRange->end())
            : Carbon::now();

        $previousStart = $currentStart->copy()->subYear();
        $previousEnd = $currentEnd->copy()->subYear();

        return [
            $currentStart,
            $currentEnd,
            $currentStart->format('d/m/Y').' - '.$currentEnd->format('d/m/Y'),
            $previousStart,
            $previousEnd,
            $previousStart->format('d/m/Y').' - '.$previousEnd->format('d/m/Y'),
        ];
    }

    protected function applyDateWindowFilter(Builder $query, ?Carbon $start, ?Carbon $end): Builder
    {
        return $query
            ->when(
                $start !== null,
                fn (Builder $builder) => $builder->whereDate('transaction_date', '>=', $start->toDateString()),
            )
            ->when(
                $end !== null,
                fn (Builder $builder) => $builder->whereDate('transaction_date', '<=', $end->toDateString()),
            );
    }

    protected function deltaPercentage(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
