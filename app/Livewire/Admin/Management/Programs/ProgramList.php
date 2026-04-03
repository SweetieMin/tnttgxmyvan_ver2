<?php

namespace App\Livewire\Admin\Management\Programs;

use App\Models\Program;
use App\Repositories\Contracts\ProgramRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ProgramList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('program-saved')]
    #[On('program-deleted')]
    #[On('program-reordered')]
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
            ->query($this->programTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('ordering')
                    ->label(__('Ordering'))
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('course')
                    ->label(__('Catechism class'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sector')
                    ->label(__('Sector'))
                    ->searchable()
                    ->sortable(),
            ])
            ->persistSortInSession()
            ->reorderableColumns()
            ->reorderable('ordering')
            ->authorizeReorder(fn (): bool => Auth::user()?->can('management.program.update') ?? false)
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : __('Enable reordering'))
                    ->color('gray')
                    ->size('sm'),
            )
            ->afterReordering(function (): void {
                Flux::toast(
                    text: __('Program order updated successfully.'),
                    heading: __('Success'),
                    variant: 'success',
                );

                $this->dispatch('program-reordered');
            })
            ->stackedOnMobile()
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy('ordering')->orderBy('id'))
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading(__('No programs found.'))
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label(__('Edit'))
                        ->color('primary')
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (): bool => Auth::user()?->can('management.program.update') ?? false)
                        ->action(function (Program $record): void {
                            $this->dispatch('edit-program', programId: $record->getKey());
                        }),
                    Action::make('delete')
                        ->label(__('Delete'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->can('management.program.delete') ?? false)
                        ->action(function (Program $record): void {
                            $this->dispatch('confirm-delete-program', programId: $record->getKey());
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
        return view('livewire.admin.management.programs.program-list');
    }

    protected function programTableQuery(): Builder
    {
        return $this->programRepository()->query();
    }

    protected function programRepository(): ProgramRepositoryInterface
    {
        return app(ProgramRepositoryInterface::class);
    }
}
