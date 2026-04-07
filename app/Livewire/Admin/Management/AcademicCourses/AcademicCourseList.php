<?php

namespace App\Livewire\Admin\Management\AcademicCourses;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Repositories\Contracts\AcademicCourseRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\On;
use Livewire\Component;

class AcademicCourseList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    #[On('academic-course-saved')]
    #[On('academic-course-deleted')]
    #[On('academic-course-reordered')]
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
            ->query($this->academicCourseTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('ordering')
                    ->label(__('Ordering'))
                    ->visibleFrom('lg'),
                TextColumn::make('academicYear.name')
                    ->label(__('Academic year'))
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('course_name')
                    ->label(__('Catechism class'))
                    ->searchable()
                    ->description(fn (AcademicCourse $record): string => $record->sector_name)
                    ->wrap(),
                TextColumn::make('sector_name')
                    ->label(__('Sector'))
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('required_scores')
                    ->label(__('Required scores'))
                    ->getStateUsing(function (AcademicCourse $record): HtmlString {
                        return new HtmlString(implode('', [
                            '<div>'.e(__('Catechism average')).': '.e(number_format((float) $record->catechism_avg_score, 2, '.', '')).'</div>',
                            '<div>'.e(__('Catechism training')).': '.e(number_format((float) $record->catechism_training_score, 2, '.', '')).'</div>',
                            '<div>'.e(__('Activity')).': '.e((string) $record->activity_score).'</div>',
                        ]));
                    })
                    ->html()
                    ->wrap()
                    ->visibleFrom('lg'),
                TextColumn::make('is_active')
                    ->label(__('Status'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('Active') : __('Inactive'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->wrap(),
            ])
            ->persistSortInSession()
            ->reorderable('ordering')
            ->authorizeReorder(fn (): bool => Auth::user()?->can('management.academic-course.update') ?? false)
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering): Action => $action
                    ->button()
                    ->label($isReordering ? __('Disable reordering') : __('Enable reordering'))
                    ->color('gray')
                    ->size('sm'),
            )
            ->afterReordering(function (): void {
                Flux::toast(
                    text: __('Catechism - sector class order updated successfully.'),
                    heading: __('Success'),
                    variant: 'success',
                );

                $this->dispatch('academic-course-reordered');
            })
            ->stackedOnMobile()
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label(__('Academic year'))
                    ->options($this->academicYearOptions())
                    ->default((string) $this->defaultAcademicYearId()),
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
            ->emptyStateHeading(__('No catechism - sector classes found.'))
            ->recordActions([
                ActionGroup::make([
                    Action::make('edit')
                        ->label(__('Edit'))
                        ->color('primary')
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (): bool => Auth::user()?->can('management.academic-course.update') ?? false)
                        ->action(function (AcademicCourse $record): void {
                            $this->dispatch('edit-academic-course', academicCourseId: $record->getKey());
                        }),
                    Action::make('duplicate')
                        ->label(__('Duplicate'))
                        ->icon('heroicon-m-document-duplicate')
                        ->visible(fn (): bool => Auth::user()?->can('management.academic-course.create') ?? false)
                        ->action(function (AcademicCourse $record): void {
                            $this->dispatch('duplicate-academic-course', academicCourseId: $record->getKey());
                        }),
                    Action::make('delete')
                        ->label(__('Delete'))
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->visible(fn (): bool => Auth::user()?->can('management.academic-course.delete') ?? false)
                        ->action(function (AcademicCourse $record): void {
                            $this->dispatch('confirm-delete-academic-course', academicCourseId: $record->getKey());
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
        return view('livewire.admin.management.academic-courses.academic-course-list');
    }

    protected function academicCourseTableQuery(): Builder
    {
        return $this->academicCourseRepository()->query();
    }

    protected function academicCourseRepository(): AcademicCourseRepositoryInterface
    {
        return app(AcademicCourseRepositoryInterface::class);
    }

    /**
     * @return array<int, string>
     */
    protected function academicYearOptions(): array
    {
        return AcademicYear::query()
            ->orderByRaw("case status_academic when 'ongoing' then 0 when 'upcoming' then 1 when 'finished' then 2 else 3 end")
            ->orderByDesc('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function defaultAcademicYearId(): ?int
    {
        /** @var AcademicYear|null $academicYear */
        $academicYear = AcademicYear::query()
            ->where('status_academic', 'ongoing')
            ->latest('id')
            ->first();

        if ($academicYear !== null) {
            return (int) $academicYear->id;
        }

        $academicYearId = AcademicYear::query()->latest('id')->value('id');

        return $academicYearId !== null ? (int) $academicYearId : null;
    }
}
