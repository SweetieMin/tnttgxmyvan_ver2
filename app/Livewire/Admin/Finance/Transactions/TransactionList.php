<?php

namespace App\Livewire\Admin\Finance\Transactions;

use App\Exports\Finance\TransactionExport as TransactionExcelExport;
use App\Models\Category;
use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('transaction-saved')]
    #[On('transaction-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    /**
     * @return array<string, mixed>
     */
    public function exportFormData(): array
    {
        return [
            'selectedTypes' => $this->selectedTypeFilter() !== '' ? [$this->selectedTypeFilter()] : $this->allTypeValues(),
            'selectedCategoryIds' => $this->selectedCategoryFilter() !== '' ? [$this->selectedCategoryFilter()] : $this->allCategoryValues(),
            'selectedStatuses' => $this->selectedStatusFilter() !== '' ? [$this->selectedStatusFilter()] : $this->allStatusValues(),
            'selectedColumns' => $this->defaultSelectedColumns(),
            'dateFrom' => $this->selectedDateFromFilter(),
            'dateUntil' => $this->selectedDateUntilFilter(),
            'fileName' => $this->defaultFileName(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function exportTransactions(array $data): BinaryFileResponse
    {
        $selectedColumns = array_values($data['selectedColumns'] ?? []);

        if (! in_array('amount', $selectedColumns, true)) {
            throw ValidationException::withMessages([
                'selectedColumns' => __('Amount column is required to export totals.'),
            ]);
        }

        return Excel::download(
            new TransactionExcelExport(
                transactions: $this->filteredTransactionsForExport($data),
                selectedColumns: $this->normalizedSelectedColumns($selectedColumns),
            ),
            $this->resolvedFileName((string) ($data['fileName'] ?? '')).'.xlsx',
        );
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->transactionTableQuery())
            ->searchable(['transaction_item', 'description', 'in_charge', 'category.name'])
            ->persistSearchInSession()
            ->striped()
            ->columns([
                TextColumn::make('transaction_date')
                    ->label(__('Transaction date'))
                    ->date('d/m/Y')
                    ->visibleFrom('lg')
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label(__('Category'))
                    ->placeholder(__('No category'))
                    ->visibleFrom('lg')
                    ->toggleable(),
                TextColumn::make('transaction_item')
                    ->label(__('Fund item'))
                    ->searchable()
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(match ($state) {
                        'expense' => 'Expense',
                        default => 'Income',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'expense' => 'danger',
                        default => 'success',
                    })
                    ->visibleFrom('lg')
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label(__('Amount'))
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.').' đ')
                    ->toggleable(),
                TextColumn::make('in_charge')
                    ->label(__('In charge'))
                    ->placeholder('—')
                    ->visibleFrom('lg')
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(match ($state) {
                        'completed' => 'Completed',
                        default => 'Pending',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        default => 'warning',
                    })
                    ->visibleFrom('lg')
                    ->toggleable(),
                TextColumn::make('file_name')
                    ->label(__('Attachment'))
                    ->getStateUsing(function (Transaction $record): HtmlString {
                        if (blank($record->file_url)) {
                            return new HtmlString('<span class="text-sm text-zinc-400">'.e(__('No file')).'</span>');
                        }

                        return new HtmlString('<span class="font-medium">'.e(__('Open file')).'</span>');
                    })
                    ->url(fn (Transaction $record): ?string => $record->file_url)
                    ->openUrlInNewTab()
                    ->html()
                    ->color(fn (Transaction $record): string => filled($record->file_url) ? 'primary' : 'gray')
                    ->visibleFrom('lg')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options($this->typeOptions()),
                SelectFilter::make('category_id')
                    ->label(__('Category'))
                    ->options($this->categoryOptions()),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options($this->statusOptions()),
                Filter::make('transaction_date')
                    ->label(__('Date range'))
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('From date'))
                            ->native(false)
                            ->closeOnDateSelection()
                            ->timezone('Asia/Ho_Chi_Minh')
                            ->displayFormat('d/m/Y')
                            ->placeholder(__('Choose form date')),
                        DatePicker::make('until')
                            ->label(__('To date'))
                            ->native(false)
                            ->closeOnDateSelection()
                            ->timezone('Asia/Ho_Chi_Minh')
                            ->displayFormat('d/m/Y')
                            ->placeholder(__('Choose to date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['from'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('transaction_date', '>=', $data['from']),
                            )
                            ->when(
                                filled($data['until'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('transaction_date', '<=', $data['until']),
                            );
                    }),
            ])
            ->filtersFormColumns(2)
            ->deferFilters(false)
            ->persistFiltersInSession()
            ->reorderableColumns()
            ->headerActions([
                Action::make('exportData')
                    ->label(__('Export'))
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn (): bool => Auth::user()?->can('finance.transaction.view') ?? false)
                    ->modalHeading(__('Export transactions'))
                    ->modalDescription(__('Choose filters and columns before exporting the common fund report to Excel.'))
                    ->modalSubmitActionLabel(__('Export Excel'))
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->fillForm(fn (): array => $this->exportFormData())
                    ->schema([
                        CheckboxList::make('selectedTypes')
                            ->label(__('Transaction type'))
                            ->options($this->typeOptions())
                            ->bulkToggleable()
                            ->columns(2),
                        CheckboxList::make('selectedCategoryIds')
                            ->label(__('Category'))
                            ->options($this->categoryOptions())
                            ->bulkToggleable()
                            ->columns(2),
                        CheckboxList::make('selectedStatuses')
                            ->label(__('Status'))
                            ->options($this->statusOptions())
                            ->bulkToggleable()
                            ->columns(2),
                        CheckboxList::make('selectedColumns')
                            ->label(__('Columns to export'))
                            ->options($this->availableColumns())
                            ->descriptions([
                                'amount' => __('Amount will be split into Income and Expense columns in Excel.'),
                            ])
                            ->bulkToggleable()
                            ->columns(2)
                            ->required(),
                        Grid::make([
                            'default' => 1,
                            'md' => 4,
                        ])
                            ->gridContainer()
                            ->dense()
                            ->schema([
                                DatePicker::make('dateFrom')
                                    ->label(__('From date'))
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->timezone('Asia/Ho_Chi_Minh')
                                    ->displayFormat('d/m/Y')
                                    ->placeholder(__('Choose form date'))
                                    ->columnSpan(1),
                                DatePicker::make('dateUntil')
                                    ->label(__('To date'))
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->timezone('Asia/Ho_Chi_Minh')
                                    ->displayFormat('d/m/Y')
                                    ->placeholder(__('Choose to date'))
                                    ->columnSpan(1),
                                TextInput::make('fileName')
                                    ->label(__('File name'))
                                    ->suffix('.xlsx')
                                    ->maxLength(255)
                                    ->placeholder(__('Common fund report'))
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->action(function (array $data): BinaryFileResponse {
                        return $this->exportTransactions($data);
                    }),
            ])
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading(__('No transactions found.'))
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label(__('Edit'))
                        ->color('primary')
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (): bool => Auth::user()?->can('finance.transaction.update') ?? false)
                        ->action(function (Transaction $record): void {
                            $this->dispatch('edit-transaction', transactionId: $record->getKey());
                        }),
                    Action::make('delete')
                        ->label(__('Delete'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->can('finance.transaction.delete') ?? false)
                        ->action(function (Transaction $record): void {
                            $this->dispatch('confirm-delete-transaction', transactionId: $record->getKey());
                        }),
                ])
                    ->button()
                    ->label(__('Actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->size('sm')
                    ->dropdownPlacement('bottom-end'),
            ])
            ->stackedOnMobile();
    }

    public function render(): View
    {
        return view('livewire.admin.finance.transactions.transaction-list');
    }

    protected function transactionRepository(): TransactionRepositoryInterface
    {
        return app(TransactionRepositoryInterface::class);
    }

    protected function transactionTableQuery(): Builder
    {
        return $this->transactionRepository()
            ->query()
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');
    }

    /**
     * @return array<string, string>
     */
    protected function typeOptions(): array
    {
        return [
            'income' => __('Income'),
            'expense' => __('Expense'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function statusOptions(): array
    {
        return [
            'pending' => __('Pending'),
            'completed' => __('Completed'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('ordering')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Category $category): array => [
                (string) $category->id => $category->name,
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function availableColumns(): array
    {
        return [
            'transaction_date' => __('Transaction date'),
            'category' => __('Category'),
            'transaction_item' => __('Fund item'),
            'amount' => __('Amount'),
            'in_charge' => __('In charge'),
            'status' => __('Status'),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function allTypeValues(): array
    {
        return array_keys($this->typeOptions());
    }

    /**
     * @return array<int, string>
     */
    protected function allCategoryValues(): array
    {
        return array_keys($this->categoryOptions());
    }

    /**
     * @return array<int, string>
     */
    protected function allStatusValues(): array
    {
        return array_keys($this->statusOptions());
    }

    /**
     * @return array<int, string>
     */
    protected function defaultSelectedColumns(): array
    {
        return array_keys($this->availableColumns());
    }

    protected function selectedTypeFilter(): string
    {
        return (string) data_get($this->tableFilters, 'type.value', '');
    }

    protected function selectedCategoryFilter(): string
    {
        return (string) data_get($this->tableFilters, 'category_id.value', '');
    }

    protected function selectedStatusFilter(): string
    {
        return (string) data_get($this->tableFilters, 'status.value', '');
    }

    protected function selectedDateFromFilter(): ?string
    {
        $value = data_get($this->tableFilters, 'transaction_date.from');

        return filled($value) ? (string) $value : null;
    }

    protected function selectedDateUntilFilter(): ?string
    {
        $value = data_get($this->tableFilters, 'transaction_date.until');

        return filled($value) ? (string) $value : null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, Transaction>
     */
    protected function filteredTransactionsForExport(array $data): Collection
    {
        $selectedTypes = array_values($data['selectedTypes'] ?? []);
        $selectedCategoryIds = array_values($data['selectedCategoryIds'] ?? []);
        $selectedStatuses = array_values($data['selectedStatuses'] ?? []);

        return Transaction::query()
            ->with('category')
            ->when($selectedTypes !== [], fn (Builder $query): Builder => $query->whereIn('type', $selectedTypes))
            ->when($selectedCategoryIds !== [], fn (Builder $query): Builder => $query->whereIn('category_id', $selectedCategoryIds))
            ->when($selectedStatuses !== [], fn (Builder $query): Builder => $query->whereIn('status', $selectedStatuses))
            ->when(filled($data['dateFrom'] ?? null), fn (Builder $query): Builder => $query->whereDate('transaction_date', '>=', $data['dateFrom']))
            ->when(filled($data['dateUntil'] ?? null), fn (Builder $query): Builder => $query->whereDate('transaction_date', '<=', $data['dateUntil']))
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
            array_keys($this->availableColumns()),
            fn (string $column): bool => in_array($column, $selectedColumns, true),
        ));
    }

    protected function defaultFileName(): string
    {
        return 'Báo cáo thu chi-'.now()->format('Ymd_His');
    }

    protected function resolvedFileName(string $fileName): string
    {
        $resolvedFileName = Str::of($fileName)
            ->trim()
            ->replaceMatches('/\.xlsx$/i', '')
            ->replaceMatches('/[\\\\\\/:"*?<>|]+/', '-')
            ->value();

        return $resolvedFileName !== '' ? $resolvedFileName : $this->defaultFileName();
    }
}
