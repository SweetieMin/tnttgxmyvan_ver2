<?php

namespace App\Livewire\Admin\Management\AcademicCourses;

use App\Foundation\PersonnelDirectory;
use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\AcademicCourseRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
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
use Illuminate\Support\Collection;
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
                    Action::make('assignCatechists')
                        ->label(__('Assign catechists'))
                        ->icon('heroicon-m-book-open')
                        ->color('info')
                        ->visible(fn (): bool => Auth::user()?->can('management.academic-course.update') ?? false)
                        ->modalHeading(__('Assign catechists'))
                        ->modalDescription(__('Choose the primary catechist and supporting catechists for this class.'))
                        ->modalSubmitActionLabel(__('Save assignments'))
                        ->fillForm(fn (AcademicCourse $record): array => $this->assignmentFormData(
                            $record,
                            'catechist',
                            'assistant_catechist',
                            'primaryCatechistId',
                            'assistantCatechistIds',
                            ['Trưởng Giáo Lý', 'Phó Giáo Lý'],
                        ))
                        ->schema([
                            Select::make('primaryCatechistId')
                                ->label(__('Primary catechist'))
                                ->options(fn (): array => $this->catechistOptions())
                                ->searchable()
                                ->preload(),
                            CheckboxList::make('assistantCatechistIds')
                                ->label(__('Supporting catechists'))
                                ->options(fn (): array => $this->catechistOptions())
                                ->bulkToggleable()
                                ->columns(2),
                        ])
                        ->action(function (AcademicCourse $record, array $data): void {
                            $this->syncAcademicCourseAssignments(
                                $record,
                                primaryAssignmentType: 'catechist',
                                assistantAssignmentType: 'assistant_catechist',
                                primaryUserId: data_get($data, 'primaryCatechistId'),
                                assistantUserIds: data_get($data, 'assistantCatechistIds', []),
                            );

                            Flux::toast(
                                text: __('Catechist assignments updated successfully.'),
                                heading: __('Success'),
                                variant: 'success',
                            );
                        }),
                    Action::make('assignLeaders')
                        ->label(__('Assign leaders'))
                        ->icon('heroicon-m-flag')
                        ->color('info')
                        ->visible(fn (): bool => Auth::user()?->can('management.academic-course.update') ?? false)
                        ->modalHeading(__('Assign leaders'))
                        ->modalDescription(__('Choose the primary leader and supporting leaders for this class.'))
                        ->modalSubmitActionLabel(__('Save assignments'))
                        ->fillForm(fn (AcademicCourse $record): array => $this->assignmentFormData(
                            $record,
                            'leader',
                            'assistant_leader',
                            'primaryLeaderId',
                            'assistantLeaderIds',
                            ['Xứ Đoàn Trưởng', 'Xứ Đoàn Phó'],
                        ))
                        ->schema([
                            Select::make('primaryLeaderId')
                                ->label(__('Primary leader'))
                                ->options(fn (): array => $this->leaderOptions())
                                ->searchable()
                                ->preload(),
                            CheckboxList::make('assistantLeaderIds')
                                ->label(__('Supporting leaders'))
                                ->options(fn (): array => $this->leaderOptions())
                                ->bulkToggleable()
                                ->columns(2),
                        ])
                        ->action(function (AcademicCourse $record, array $data): void {
                            $this->syncAcademicCourseAssignments(
                                $record,
                                primaryAssignmentType: 'leader',
                                assistantAssignmentType: 'assistant_leader',
                                primaryUserId: data_get($data, 'primaryLeaderId'),
                                assistantUserIds: data_get($data, 'assistantLeaderIds', []),
                            );

                            Flux::toast(
                                text: __('Leader assignments updated successfully.'),
                                heading: __('Success'),
                                variant: 'success',
                            );
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

    /**
     * @return array{primaryCatechistId?: int|null, assistantCatechistIds?: array<int, int>, primaryLeaderId?: int|null, assistantLeaderIds?: array<int, int>}
     */
    protected function assignmentFormData(
        AcademicCourse $record,
        string $primaryAssignmentType,
        string $assistantAssignmentType,
        string $primaryField,
        string $assistantField,
        array $defaultAssistantRoleNames,
    ): array {
        $assignments = $record->staffAssignments()
            ->whereIn('assignment_type', [$primaryAssignmentType, $assistantAssignmentType])
            ->get();

        $primaryUserId = $assignments
            ->firstWhere('assignment_type', $primaryAssignmentType)?->user_id;

        /** @var array<int, int> $assistantUserIds */
        $assistantUserIds = $assignments
            ->where('assignment_type', $assistantAssignmentType)
            ->pluck('user_id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();

        return [
            $primaryField => $primaryUserId,
            $assistantField => $assistantUserIds !== []
                ? $assistantUserIds
                : $this->defaultAssignmentUserIds($defaultAssistantRoleNames),
        ];
    }

    /**
     * @param  array<int, mixed>  $assistantUserIds
     */
    protected function syncAcademicCourseAssignments(
        AcademicCourse $record,
        string $primaryAssignmentType,
        string $assistantAssignmentType,
        mixed $primaryUserId,
        array $assistantUserIds,
    ): void {
        $normalizedPrimaryUserId = is_numeric($primaryUserId) ? (int) $primaryUserId : null;
        $normalizedAssistantUserIds = collect($assistantUserIds)
            ->filter(fn (mixed $userId): bool => is_numeric($userId))
            ->map(fn (mixed $userId): int => (int) $userId)
            ->when(
                $normalizedPrimaryUserId !== null,
                fn (Collection $userIds): Collection => $userIds->reject(fn (int $userId): bool => $userId === $normalizedPrimaryUserId),
            )
            ->unique()
            ->values();

        $record->staffAssignments()
            ->whereIn('assignment_type', [$primaryAssignmentType, $assistantAssignmentType])
            ->delete();

        $assignments = [];

        if ($normalizedPrimaryUserId !== null) {
            $assignments[] = [
                'user_id' => $normalizedPrimaryUserId,
                'assignment_type' => $primaryAssignmentType,
                'is_primary' => true,
                'assigned_by' => Auth::id(),
            ];
        }

        foreach ($normalizedAssistantUserIds as $assistantUserId) {
            $assignments[] = [
                'user_id' => $assistantUserId,
                'assignment_type' => $assistantAssignmentType,
                'is_primary' => false,
                'assigned_by' => Auth::id(),
            ];
        }

        if ($assignments !== []) {
            $record->staffAssignments()->createMany($assignments);
        }
    }

    /**
     * @return array<int, string>
     */
    protected function catechistOptions(): array
    {
        return $this->personnelOptionsForGroup('catechists');
    }

    /**
     * @return array<int, string>
     */
    protected function leaderOptions(): array
    {
        return $this->personnelOptionsForGroup('leaders');
    }

    /**
     * @return array<int, string>
     */
    protected function personnelOptionsForGroup(string $group): array
    {
        $roleNames = $this->personnelDirectory()->roleNamesForGroup($group);

        if ($roleNames === []) {
            return [];
        }

        return User::query()
            ->with('roles:id,name')
            ->whereHas('roles', function (Builder $query) use ($roleNames): void {
                $query->whereIn('name', $roleNames);
            })
            ->orderBy('last_name')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (User $user): string => sprintf(
                '%05d|%s|%s',
                $this->contextRoleIdForGroup($user, $roleNames),
                mb_strtolower($user->last_name),
                mb_strtolower($user->name),
            ))
            ->mapWithKeys(fn (User $user): array => [
                $user->id => $user->full_name.' - '.$this->groupRoleLabel($user, $roleNames),
            ])
            ->all();
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    protected function contextRoleIdForGroup(User $user, array $roleNames): int
    {
        return (int) ($user->roles
            ->filter(fn (Role $role): bool => in_array($role->name, $roleNames, true))
            ->pluck('id')
            ->min() ?? PHP_INT_MAX);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    protected function groupRoleLabel(User $user, array $roleNames): string
    {
        return $user->roles
            ->filter(fn (Role $role): bool => in_array($role->name, $roleNames, true))
            ->sortBy('id')
            ->pluck('name')
            ->first() ?? __('Unknown');
    }

    protected function personnelDirectory(): PersonnelDirectory
    {
        return app(PersonnelDirectory::class);
    }

    /**
     * @param  array<int, string>  $roleNames
     * @return array<int, int>
     */
    protected function defaultAssignmentUserIds(array $roleNames): array
    {
        if ($roleNames === []) {
            return [];
        }

        return User::query()
            ->whereHas('roles', function (Builder $query) use ($roleNames): void {
                $query->whereIn('name', $roleNames);
            })
            ->orderBy('last_name')
            ->orderBy('name')
            ->pluck('id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
    }
}
