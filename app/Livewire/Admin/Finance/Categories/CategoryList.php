<?php

namespace App\Livewire\Admin\Finance\Categories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoryList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('category-saved')]
    #[On('category-deleted')]
    #[On('category-reordered')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->categoryTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('ordering')
                    ->label(__('Ordering'))
                    ->visibleFrom('lg'),
                TextColumn::make('name')
                    ->label(__('Category'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->searchable()
                    ->placeholder('—')
                    ->visibleFrom('lg'),
                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Active') : __('Inactive'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
            ])
            ->persistSortInSession()
            ->reorderable('ordering')
            ->authorizeReorder(fn (): bool => Auth::user()?->can('finance.category.update') ?? false)
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : __('Enable reordering'))
                    ->color('gray')
                    ->size('sm'),
            )
            ->afterReordering(function (): void {
                Flux::toast(
                    text: __('Category order updated successfully.'),
                    heading: __('Success'),
                    variant: 'success',
                );

                $this->dispatch('category-reordered');
            })
            ->stackedOnMobile()
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('Status'))
                    ->options([
                        '1' => __('Active'),
                        '0' => __('Inactive'),
                    ]),
            ])
            ->deferFilters(false)
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('ordering')->orderBy('id'))
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading(__('No categories found.'))
            ->selectable()
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('updateStatus')
                        ->label(__('Change selected status'))
                        ->icon('heroicon-m-pencil-square')
                        ->color('info')
                        ->visible(fn (): bool => Auth::user()?->can('finance.category.update') ?? false)
                        ->requiresConfirmation()
                        ->schema([
                            Select::make('is_active')
                                ->label(__('New status'))
                                ->options([
                                    '1' => __('Active'),
                                    '0' => __('Inactive'),
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Category $record) use ($data): void {
                                $record->update([
                                    'is_active' => (bool) $data['is_active'],
                                ]);
                            });

                            Flux::toast(
                                text: __('Selected categories updated successfully.'),
                                heading: __('Success'),
                                variant: 'success',
                            );

                            $this->dispatch('category-saved');
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label(__('Edit'))
                        ->color('primary')
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (): bool => Auth::user()?->can('finance.category.update') ?? false)
                        ->action(function (Category $record): void {
                            $this->dispatch('edit-category', categoryId: $record->getKey());
                        }),
                    Action::make('delete')
                        ->label(__('Delete'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->can('finance.category.delete') ?? false)
                        ->action(function (Category $record): void {
                            $this->dispatch('confirm-delete-category', categoryId: $record->getKey());
                        }),
                ])
                    ->button()
                    ->label(__('Actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->size('sm')
                    ->dropdownPlacement('bottom-end'),
            ]);
    }

    public function render(): View
    {
        return view('livewire.admin.finance.categories.category-list');
    }

    protected function categoryTableQuery(): Builder
    {
        return $this->categoryRepository()->query();
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return app(CategoryRepositoryInterface::class);
    }
}
