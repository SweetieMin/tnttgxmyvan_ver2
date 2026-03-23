<?php

namespace App\Livewire\Admin\Finance\Transactions;

use App\Exports\Finance\TransactionExport as TransactionExcelExport;
use App\Models\Category;
use App\Models\Transaction;
use Flux\DateRange;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionExport extends Component
{
    public bool $showExportModal = false;

    public array $selectedTypes = [];

    public array $selectedCategoryIds = [];

    public array $selectedStatuses = [];

    public array $selectedColumns = [];

    public string $fileName = '';

    public ?DateRange $dateRange = null;

    public function mount(): void
    {
        $this->selectedColumns = $this->defaultSelectedColumns();
        $this->fileName = $this->defaultFileName();
    }

    #[On('open-transaction-export-modal')]
    public function openExportModal(
        string $selectedType = '',
        string $selectedCategory = '',
        string $selectedStatus = '',
    ): void {
        $this->ensureCanViewTransactions();

        $this->selectedTypes = $selectedType !== '' ? [$selectedType] : $this->allTypeValues();
        $this->selectedCategoryIds = $selectedCategory !== '' ? [$selectedCategory] : $this->allCategoryValues();
        $this->selectedStatuses = $selectedStatus !== '' ? [$selectedStatus] : $this->allStatusValues();
        $this->selectedColumns = $this->defaultSelectedColumns();
        $this->fileName = $this->defaultFileName();
        $this->dateRange = null;
        $this->resetErrorBag();
        $this->showExportModal = true;
    }

    public function closeExportModal(): void
    {
        $this->showExportModal = false;
        $this->resetErrorBag();
    }

    public function selectAllTypes(): void
    {
        $this->selectedTypes = $this->allTypeValues();
    }

    public function selectAllCategories(): void
    {
        $this->selectedCategoryIds = $this->allCategoryValues();
    }

    public function selectAllStatuses(): void
    {
        $this->selectedStatuses = $this->allStatusValues();
    }

    public function selectAllColumns(): void
    {
        $this->selectedColumns = $this->defaultSelectedColumns();
    }

    public function exportTransactions(): ?BinaryFileResponse
    {
        $this->ensureCanViewTransactions();

        $validated = $this->validate();

        if (! in_array('amount', $validated['selectedColumns'], true)) {
            $this->addError('selectedColumns', __('Amount column is required to export totals.'));

            return null;
        }

        $this->showExportModal = false;

        return Excel::download(
            new TransactionExcelExport(
                transactions: $this->filteredTransactions(),
                selectedColumns: $this->normalizedSelectedColumns($validated['selectedColumns']),
            ),
            $this->resolvedFileName().'.xlsx',
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'selectedTypes' => ['array'],
            'selectedTypes.*' => ['string', Rule::in(['income', 'expense'])],
            'selectedCategoryIds' => ['array'],
            'selectedCategoryIds.*' => ['string', Rule::exists(Category::class, 'id')],
            'selectedStatuses' => ['array'],
            'selectedStatuses.*' => ['string', Rule::in(['pending', 'completed'])],
            'selectedColumns' => ['required', 'array', 'min:1'],
            'selectedColumns.*' => ['string', Rule::in(array_keys($this->availableColumns()->all()))],
            'fileName' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'selectedColumns.required' => __('Please choose at least one column to export.'),
            'selectedColumns.min' => __('Please choose at least one column to export.'),
            'selectedColumns.*.in' => __('The selected export column is invalid.'),
            'selectedTypes.*.in' => __('The selected transaction type is invalid.'),
            'selectedCategoryIds.*.exists' => __('Selected category is invalid.'),
            'selectedStatuses.*.in' => __('The selected transaction status is invalid.'),
            'fileName.max' => __('The file name may not be greater than 255 characters.'),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function defaultSelectedColumns(): array
    {
        return array_keys($this->availableColumns()->all());
    }

    /**
     * @return array<int, string>
     */
    protected function allTypeValues(): array
    {
        return $this->typeOptions()->pluck('value')->all();
    }

    /**
     * @return array<int, string>
     */
    protected function allCategoryValues(): array
    {
        return $this->categoryOptions()->pluck('value')->all();
    }

    /**
     * @return array<int, string>
     */
    protected function allStatusValues(): array
    {
        return $this->statusOptions()->pluck('value')->all();
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    public function typeOptions(): Collection
    {
        return collect([
            ['value' => 'income', 'label' => __('Income')],
            ['value' => 'expense', 'label' => __('Expense')],
        ]);
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    public function statusOptions(): Collection
    {
        return collect([
            ['value' => 'pending', 'label' => __('Pending')],
            ['value' => 'completed', 'label' => __('Completed')],
        ]);
    }

    /**
     * @return Collection<int, array{value: string, label: string}>
     */
    public function categoryOptions(): Collection
    {
        return Category::query()
            ->orderBy('ordering')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'value' => (string) $category->id,
                'label' => $category->name,
            ]);
    }

    /**
     * @return Collection<string, string>
     */
    public function availableColumns(): Collection
    {
        return collect([
            'transaction_date' => __('Transaction date'),
            'category' => __('Category'),
            'transaction_item' => __('Fund item'),
            'amount' => __('Amount'),
            'in_charge' => __('In charge'),
            'status' => __('Status'),
        ]);
    }

    /**
     * @return Collection<int, Transaction>
     */
    protected function filteredTransactions(): Collection
    {
        return Transaction::query()
            ->with('category')
            ->when($this->selectedTypes !== [], fn ($query) => $query->whereIn('type', $this->selectedTypes))
            ->when($this->selectedCategoryIds !== [], fn ($query) => $query->whereIn('category_id', $this->selectedCategoryIds))
            ->when($this->selectedStatuses !== [], fn ($query) => $query->whereIn('status', $this->selectedStatuses))
            ->when($this->dateRange?->hasStart(), fn ($query) => $query->whereDate('transaction_date', '>=', $this->dateRange?->start()?->toDateString()))
            ->when($this->dateRange?->hasEnd(), fn ($query) => $query->whereDate('transaction_date', '<=', $this->dateRange?->end()?->toDateString()))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  array<int, string>  $selectedColumns
     * @return array<int, string>
     */
    protected function normalizedSelectedColumns(array $selectedColumns): array
    {
        return array_values(array_filter(
            array_keys($this->availableColumns()->all()),
            fn (string $column): bool => in_array($column, $selectedColumns, true),
        ));
    }

    protected function defaultFileName(): string
    {
        return 'Báo cáo thu chi-'.now()->format('Ymd_His');
    }

    protected function resolvedFileName(): string
    {
        $fileName = Str::of($this->fileName)
            ->trim()
            ->replaceMatches('/\.xlsx$/i', '')
            ->replaceMatches('/[\\\\\\/:"*?<>|]+/', '-')
            ->value();

        return $fileName !== '' ? $fileName : $this->defaultFileName();
    }

    protected function ensureCanViewTransactions(): void
    {
        abort_unless((bool) Auth::user()?->can('finance.transaction.view'), 403);
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-export');
    }
}
