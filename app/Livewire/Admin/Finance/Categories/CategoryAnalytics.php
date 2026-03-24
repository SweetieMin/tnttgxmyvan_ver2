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

    public function mount(): void
    {
        $this->dateRange = DateRange::yearToDate();
    }

    public function applyFilters(): void {}

    public function resetFilters(): void
    {
        $this->selectedCategory = '';
        $this->dateRange = DateRange::yearToDate();
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    public function categoryOptions(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('ordering')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'value' => (string) $category->id,
                'label' => $category->name,
            ]);
    }

    public function render(): View
    {
        $categoryOptions = $this->categoryOptions();
        $categorySummaries = $this->categorySummaries();
        $overviewCards = $this->overviewCards();
        $categoryBarChart = $this->categoryBarChart($categorySummaries);
        $expenseShareBreakdown = $this->expenseShareBreakdown($categorySummaries);
        $timeTrendChart = $this->timeTrendChart();

        return view('livewire.admin.finance.categories.category-analytics', [
            'categoryOptions' => $categoryOptions,
            'categorySummaries' => $categorySummaries,
            'overviewCards' => $overviewCards,
            'categoryBarChart' => $categoryBarChart,
            'expenseShareBreakdown' => $expenseShareBreakdown,
            'timeTrendChart' => $timeTrendChart,
            'appliedCategoriesLabel' => $this->appliedCategoriesLabel(),
            'appliedDateRangeLabel' => $this->appliedDateRangeLabel(),
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
        $previousYearBalance = $this->previousYearBalance();

        return [
            [
                'label' => __('Previous year fund balance'),
                'value' => $this->formatMoney($previousYearBalance),
                'tone' => $previousYearBalance >= 0 ? 'sky' : 'rose',
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

    protected function previousYearBalance(): int
    {
        $previousYear = Carbon::now()->subYear()->year;
        $categorySegment = $this->selectedCategory !== '' ? $this->selectedCategory : 'all';

        return (int) Cache::remember(
            "finance.category-analytics.previous-year-balance.{$previousYear}.{$categorySegment}",
            now()->addHour(),
            function () use ($previousYear): int {
                $totals = Transaction::query()
                    ->when(
                        $this->selectedCategory !== '',
                        fn (Builder $query) => $query->where('category_id', $this->selectedCategory),
                    )
                    ->whereYear('transaction_date', $previousYear)
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income")
                    ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expense")
                    ->first();

                $totalIncome = (int) ($totals?->total_income ?? 0);
                $totalExpense = (int) ($totals?->total_expense ?? 0);

                return $totalIncome - $totalExpense;
            },
        );
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
     * @return array<int, array{category: string, income: int, expense: int}>
     */
    public function categoryBarChart(Collection $categorySummaries): array
    {
        return $categorySummaries
            ->filter(fn (array $summary): bool => $summary['total_income'] > 0 || $summary['total_expense'] > 0)
            ->map(fn (array $summary): array => [
                'category' => $summary['name'],
                'income' => $summary['total_income'],
                'expense' => $summary['total_expense'],
            ])
            ->values()
            ->all();
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
    }

    protected function transactionQuery(): Builder
    {
        return $this->applyDateRangeFilter(
            Transaction::query()
                ->when(
                    $this->selectedCategory !== '',
                    fn (Builder $query) => $query->where('category_id', $this->selectedCategory),
                ),
            $this->dateRange,
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
}
