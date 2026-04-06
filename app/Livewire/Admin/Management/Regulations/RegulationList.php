<?php

namespace App\Livewire\Admin\Management\Regulations;

use App\Models\Regulation;
use App\Repositories\Contracts\RegulationRepositoryInterface;
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

class RegulationList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('regulation-saved')]
    #[On('regulation-deleted')]
    #[On('regulation-reordered')]
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
            ->query($this->regulationTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('ordering')
                    ->label(__('Ordering'))
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('description')
                    ->label(__('Description'))
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(match ($state) {
                        'minus' => 'Penalty',
                        default => 'Bonus',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'minus' => 'danger',
                        default => 'success',
                    })
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('points')
                    ->label(__('Points'))
                    ->alignCenter()
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(match ($state) {
                        'applied' => 'Applied',
                        'not_applied' => 'Not applied',
                        default => 'Pending',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'applied' => 'success',
                        'not_applied' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),
            ])
            ->persistSortInSession()
            ->reorderableColumns()
            ->reorderable('ordering')
            ->authorizeReorder(fn (): bool => Auth::user()?->can('management.regulation.update') ?? false)
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : __('Enable reordering'))
                    ->color('gray')
                    ->size('sm'),
            )
            ->afterReordering(function (): void {
                Flux::toast(
                    text: __('Regulation order updated successfully.'),
                    heading: __('Success'),
                    variant: 'success',
                );

                $this->dispatch('regulation-reordered');
            })
            ->stackedOnMobile()
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Type'))
                    ->options([
                        'plus' => __('Bonus'),
                        'minus' => __('Penalty'),
                    ]),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'pending' => __('Pending'),
                        'applied' => __('Applied'),
                        'not_applied' => __('Not applied'),
                    ]),
            ])
            ->deferFilters(false)
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('ordering')->orderBy('id'))
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading(__('No regulations found.'))
            ->selectable()
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('updateStatus')
                        ->label(__('Change selected status'))
                        ->icon('heroicon-m-pencil-square')
                        ->color('info')
                        ->visible(fn (): bool => Auth::user()?->can('management.regulation.update') ?? false)
                        ->requiresConfirmation()
                        ->schema([
                            Select::make('status')
                                ->label(__('New status'))
                                ->options([
                                    'pending' => __('Pending'),
                                    'applied' => __('Applied'),
                                    'not_applied' => __('Not applied'),
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(function (Regulation $record) use ($data): void {
                                $record->update([
                                    'status' => $data['status'],
                                ]);
                            });

                            Flux::toast(
                                text: __('Selected regulations updated successfully.'),
                                heading: __('Success'),
                                variant: 'success',
                            );

                            $this->dispatch('regulation-saved');
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deleteSelected')
                        ->label(__('Delete selected'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->can('management.regulation.delete') ?? false)
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(function (Regulation $record): void {
                                $this->regulationRepository()->delete($record);
                            });

                            Flux::toast(
                                text: __('Selected regulations deleted successfully.'),
                                heading: __('Success'),
                                variant: 'success',
                            );

                            $this->dispatch('regulation-deleted');
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
                        ->visible(fn (): bool => Auth::user()?->can('management.regulation.update') ?? false)
                        ->action(function (Regulation $record): void {
                            $this->dispatch('edit-regulation', regulationId: $record->getKey());
                        }),
                    Action::make('delete')
                        ->label(__('Delete'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->can('management.regulation.delete') ?? false)
                        ->action(function (Regulation $record): void {
                            $this->dispatch('confirm-delete-regulation', regulationId: $record->getKey());
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
        return view('livewire.admin.management.regulations.regulation-list');
    }

    protected function regulationTableQuery(): Builder
    {
        return $this->regulationRepository()->query();
    }

    protected function regulationRepository(): RegulationRepositoryInterface
    {
        return app(RegulationRepositoryInterface::class);
    }
}
