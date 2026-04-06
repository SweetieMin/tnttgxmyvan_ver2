<?php

namespace App\Livewire\Admin\Management\AcademicYear;

use App\Models\AcademicYear;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\TableComponent;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;

class AcademicYearList extends TableComponent
{
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
            ->query(
                AcademicYear::query()
                    ->orderByRaw("
                        case status_academic
                            when 'ongoing' then 1
                            when 'upcoming' then 2
                            when 'finished' then 3
                            else 4
                        end
                    ")
                    ->orderByDesc('catechism_start_date')
                    ->orderByDesc('id')
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('Academic year'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('catechism_period')
                    ->label(__('Catechism period'))
                    ->placeholder(__('N/A'))
                    ->visibleFrom('lg'),
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
                    ->visibleFrom('lg'),
                TextColumn::make('activity_score')
                    ->label(__('Activity score'))
                    ->visibleFrom('lg'),
                TextColumn::make('status_academic')
                    ->label(__('Status'))
                    ->formatStateUsing(fn (AcademicYear $record): string => __($record->status_academic_label))
                    ->color(fn (AcademicYear $record): string => $record->status_academic_color)
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_academic')
                    ->label(__('Status'))
                    ->options([
                        'ongoing' => __('Ongoing'),
                        'upcoming' => __('Upcoming'),
                        'finished' => __('Finished'),
                    ]),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label(__('Edit'))
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (): bool => auth()->user()?->can('management.academic-year.update') ?? false)
                        ->dispatch('edit-academic-year', [
                            'academicYearId' => fn (AcademicYear $record): int => $record->getKey(),
                        ]),
                    Action::make('delete')
                        ->label(__('Delete'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => auth()->user()?->can('management.academic-year.delete') ?? false)
                        ->dispatch('confirm-delete-academic-year', [
                            'academicYearId' => fn (AcademicYear $record): int => $record->getKey(),
                        ]),
                ])
                    ->button()
                    ->label(__('Actions'))
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->dropdownPlacement('bottom-end'),
            ])
            ->actionsColumnLabel(__('Actions'))
            ->emptyStateHeading(__('No academic years found.'))
            ->searchable()
            ->stackedOnMobile()
            ->paginationPageOptions([15, 25, 50])
            ->defaultPaginationPageOption(15);
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-year.academic-year-list');
    }
}
