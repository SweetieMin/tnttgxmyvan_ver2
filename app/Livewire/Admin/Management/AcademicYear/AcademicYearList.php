<?php

namespace App\Livewire\Admin\Management\AcademicYear;

use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AcademicYearList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('academic-year-saved')]
    #[On('academic-year-deleted')]
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
            ->query($this->academicYearTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label(__('Academic year'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('catechism_period')
                    ->label(__('Catechism period'))
                    ->placeholder(__('N/A'))
                    ->visibleFrom('md'),
                TextColumn::make('catechism_avg_score')
                    ->label(__('Catechism average score'))
                    ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2, ',', '.'))
                    ->visibleFrom('lg'),
                TextColumn::make('catechism_training_score')
                    ->label(__('Catechism training score'))
                    ->formatStateUsing(fn (mixed $state): string => number_format((float) $state, 2, ',', '.'))
                    ->visibleFrom('lg'),
                TextColumn::make('activity_period')
                    ->label(__('Activity period'))
                    ->placeholder(__('N/A'))
                    ->visibleFrom('md'),
                TextColumn::make('activity_score')
                    ->label(__('Activity score'))
                    ->visibleFrom('sm'),
                TextColumn::make('status_academic')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __(match ($state) {
                        'ongoing' => 'Ongoing',
                        'finished' => 'Finished',
                        default => 'Upcoming',
                    }))
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'warning',
                        'ongoing' => 'success',
                        'finished' => 'zinc',
                        default => 'warning',
                    })
                    ->sortable(),
            ])
            ->stackedOnMobile()
            ->filters([
                SelectFilter::make('status_academic')
                    ->label(__('Status'))
                    ->options([
                        'upcoming' => __('Upcoming'),
                        'ongoing' => __('Ongoing'),
                        'finished' => __('Finished'),
                    ]),
            ])
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderByRaw("
                        case status_academic
                            when 'ongoing' then 1
                            when 'upcoming' then 2
                            when 'finished' then 3
                            else 4
                        end
                    ")
                    ->orderByDesc('catechism_start_date')
                    ->orderByDesc('id');
            })
            ->deferFilters(false)
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading(__('No academic years found.'))
            ->recordActions([
                Action::make('edit')
                    ->label(__('Edit'))
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn (): bool => Auth::user()?->can('management.academic-year.update') ?? false)
                    ->action(function (AcademicYear $record): void {
                        $this->dispatch('edit-academic-year', academicYearId: $record->getKey());
                    }),
                Action::make('delete')
                    ->label(__('Delete'))
                    ->button()
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->visible(fn (): bool => Auth::user()?->can('management.academic-year.delete') ?? false)
                    ->action(function (AcademicYear $record): void {
                        $this->dispatch('confirm-delete-academic-year', academicYearId: $record->getKey());
                    }),
            ]);
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-year.academic-year-list');
    }

    protected function academicYearTableQuery(): Builder
    {
        return $this->academicYearRepository()->query();
    }

    protected function academicYearRepository(): AcademicYearRepositoryInterface
    {
        return app(AcademicYearRepositoryInterface::class);
    }
}
