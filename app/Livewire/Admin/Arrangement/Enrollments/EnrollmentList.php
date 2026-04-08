<?php

namespace App\Livewire\Admin\Arrangement\Enrollments;

use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\UserReligiousProfile;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EnrollmentList extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public int $academicYearId;

    public string $search = '';

    public string $assignmentFilter = 'all';

    public int|string $classFilterAcademicCourseId = '';

    public string $previousResultFilter = 'passed';

    /**
     * @var array<int, int>
     */
    public array $selectedUserIds = [];

    public int|string $bulkAcademicCourseId = '';

    /**
     * @var array<int, int|string>
     */
    public array $courseSelections = [];

    /**
     * @var array<int, int|string>
     */
    public array $originalCourseSelections = [];

    public function mount(int $academicYearId): void
    {
        $this->academicYearId = $academicYearId;
        $this->syncCourseSelections();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function selectVisibleChildren(): void
    {
        $this->selectedUserIds = $this->visibleRows()
            ->reject(fn (array $row): bool => $row['is_graduation_candidate'])
            ->pluck('user.id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
    }

    public function clearSelection(): void
    {
        $this->selectedUserIds = [];
        $this->bulkAcademicCourseId = '';
    }

    public function applyBulkCourseSelection(): void
    {
        $this->ensureCanUpdate();

        $selectedCourseId = $this->normalizeNullableInt($this->bulkAcademicCourseId);

        if ($selectedCourseId === null) {
            Flux::toast(
                heading: __('Warning'),
                text: __('Choose a class before applying a bulk assignment.'),
                variant: 'warning',
            );

            return;
        }

        if ($this->selectedUserIds === []) {
            Flux::toast(
                heading: __('Warning'),
                text: __('Select at least one child before applying a bulk assignment.'),
                variant: 'warning',
            );

            return;
        }

        if (! $this->academicCourses()->contains('id', $selectedCourseId)) {
            Flux::toast(
                heading: __('Warning'),
                text: __('The selected class does not belong to this academic year.'),
                variant: 'warning',
            );

            return;
        }

        $graduationCandidateUserIds = $this->visibleRows()
            ->filter(fn (array $row): bool => $row['is_graduation_candidate'])
            ->pluck('user.id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->all();
        $appliedCount = 0;
        $skippedCount = 0;

        foreach ($this->selectedUserIds as $userId) {
            if (in_array($userId, $graduationCandidateUserIds, true)) {
                $skippedCount++;

                continue;
            }

            $this->courseSelections[$userId] = $selectedCourseId;
            $appliedCount++;
        }

        Flux::toast(
            heading: __('Success'),
            text: __('Applied the selected class to :count children. Skipped :skipped graduation candidates.', [
                'count' => $appliedCount,
                'skipped' => $skippedCount,
            ]),
            variant: 'success',
        );
    }

    public function applyPromotionSuggestions(): void
    {
        $this->ensureCanUpdate();

        $previousAcademicYear = $this->previousAcademicYear();

        if ($previousAcademicYear === null) {
            Flux::toast(
                heading: __('Warning'),
                text: __('No previous academic year is available for promotion suggestions.'),
                variant: 'warning',
            );

            return;
        }

        $academicCoursesByOrdering = $this->academicCourses()->keyBy('ordering');
        $suggestedCount = 0;
        $completedCount = 0;

        foreach ($this->visibleRows() as $row) {
            $selectedCourseId = $this->normalizeNullableInt($row['selected_course_id']);

            if ($selectedCourseId !== null) {
                continue;
            }

            /** @var AcademicEnrollment|null $previousEnrollment */
            $previousEnrollment = $row['previous_enrollment'];
            $suggestedCourse = $this->suggestedCourseFor($previousEnrollment, $academicCoursesByOrdering);

            if ($suggestedCourse === null) {
                if ($this->isCompletedProgram($previousEnrollment)) {
                    $completedCount++;
                }

                continue;
            }

            $this->courseSelections[$row['user']->id] = $suggestedCourse->id;
            $suggestedCount++;
        }

        Flux::toast(
            heading: __('Success'),
            text: __('Prepared :suggested suggested assignments. :completed children have completed the final class and were left unassigned.', [
                'suggested' => $suggestedCount,
                'completed' => $completedCount,
            ]),
            variant: 'success',
        );
    }

    public function saveAssignments(): void
    {
        $this->ensureCanUpdate();

        $academicCourses = $this->academicCourses();
        $allowedCourseIds = $academicCourses
            ->pluck('id')
            ->map(fn (mixed $courseId): int => (int) $courseId)
            ->all();
        $normalizedSelections = $this->normalizedCourseSelections();

        Validator::make(
            ['courseSelections' => $normalizedSelections],
            ['courseSelections.*' => ['nullable', Rule::in($allowedCourseIds)]],
        )->validate();

        $childUserIds = $this->childUserIds();
        $previousAcademicYearId = $this->previousAcademicYear()?->id;
        $existingEnrollments = AcademicEnrollment::query()
            ->where('academic_year_id', $this->academicYearId)
            ->whereIn('user_id', $childUserIds)
            ->withCount([
                'attendanceCheckins',
                'semesterScores',
                'activityPoints',
                'promotionReview',
            ])
            ->get()
            ->keyBy('user_id');
        $previousEnrollments = $previousAcademicYearId === null
            ? collect()
            : AcademicEnrollment::query()
                ->where('academic_year_id', $previousAcademicYearId)
                ->whereIn('user_id', $childUserIds)
                ->with('academicCourse:id,ordering')
                ->get()
                ->keyBy('user_id');
        $finalProgramOrdering = (int) $academicCourses->max('ordering');

        $createdCount = 0;
        $updatedCount = 0;
        $deletedCount = 0;
        $blockedCount = 0;

        foreach ($childUserIds as $userId) {
            $selectedCourseId = $normalizedSelections[$userId] ?? null;
            /** @var AcademicEnrollment|null $existingEnrollment */
            $existingEnrollment = $existingEnrollments->get($userId);
            /** @var AcademicEnrollment|null $previousEnrollment */
            $previousEnrollment = $previousEnrollments->get($userId);

            $isGraduationCandidate = $this->isCompletedProgram($previousEnrollment, $finalProgramOrdering);

            if ($isGraduationCandidate) {
                $selectedCourseId = null;
            }

            if ($selectedCourseId === null) {
                if ($existingEnrollment === null) {
                    $this->syncReligiousStudyStatus($userId, $selectedCourseId, $previousEnrollment, $finalProgramOrdering);

                    continue;
                }

                if ($this->enrollmentHasRecordedData($existingEnrollment)) {
                    $blockedCount++;

                    continue;
                }

                $existingEnrollment->delete();
                $deletedCount++;
                $this->syncReligiousStudyStatus($userId, $selectedCourseId, $previousEnrollment, $finalProgramOrdering);

                continue;
            }

            if ($existingEnrollment === null) {
                AcademicEnrollment::query()->create([
                    'user_id' => $userId,
                    'academic_year_id' => $this->academicYearId,
                    'academic_course_id' => $selectedCourseId,
                    'status' => 'studying',
                    'review_status' => 'not_required',
                ]);

                $createdCount++;
                $this->syncReligiousStudyStatus($userId, $selectedCourseId, $previousEnrollment, $finalProgramOrdering);

                continue;
            }

            if ((int) $existingEnrollment->academic_course_id === $selectedCourseId) {
                $this->syncReligiousStudyStatus($userId, $selectedCourseId, $previousEnrollment, $finalProgramOrdering);

                continue;
            }

            $existingEnrollment->update([
                'academic_course_id' => $selectedCourseId,
            ]);

            $updatedCount++;
            $this->syncReligiousStudyStatus($userId, $selectedCourseId, $previousEnrollment, $finalProgramOrdering);
        }

        $this->syncCourseSelections();
        $this->clearSelection();

        Flux::toast(
            heading: __('Success'),
            text: __('Saved enrollments. Created: :created, updated: :updated, removed: :deleted, protected: :blocked.', [
                'created' => $createdCount,
                'updated' => $updatedCount,
                'deleted' => $deletedCount,
                'blocked' => $blockedCount,
            ]),
            variant: 'success',
        );
    }

    /**
     * @return Collection<int, AcademicCourse>
     */
    public function academicCourses(): Collection
    {
        return AcademicCourse::query()
            ->where('academic_year_id', $this->academicYearId)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    public function academicYear(): ?AcademicYear
    {
        return AcademicYear::query()->find($this->academicYearId);
    }

    public function previousAcademicYear(): ?AcademicYear
    {
        $academicYear = $this->academicYear();

        if ($academicYear === null) {
            return null;
        }

        /** @var AcademicYear|null $previousAcademicYear */
        $previousAcademicYear = AcademicYear::query()
            ->whereDate('catechism_start_date', '<', $academicYear->catechism_start_date?->toDateString())
            ->orderByDesc('catechism_start_date')
            ->first();

        return $previousAcademicYear;
    }

    public function canUpdateAssignments(): bool
    {
        return Auth::user()?->can('arrangement.enrollment.update') ?? false;
    }

    /**
     * @return array<int, string>
     */
    public function courseOptions(): array
    {
        return $this->academicCourses()
            ->mapWithKeys(fn (AcademicCourse $course): array => [
                $course->id => $course->course_name.' - '.$course->sector_name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function overviewStats(): array
    {
        $rows = $this->visibleRows();

        return [
            [
                'label' => __('Visible children'),
                'value' => $rows->count(),
            ],
            [
                'label' => __('Assigned children'),
                'value' => $rows->filter(fn (array $row): bool => $row['selected_course_id'] !== null)->count(),
            ],
            [
                'label' => __('Unassigned children'),
                'value' => $rows->filter(fn (array $row): bool => $row['selected_course_id'] === null)->count(),
            ],
            [
                'label' => __('Promotion suggestions'),
                'value' => $rows
                    ->filter(fn (array $row): bool => $row['selected_course_id'] === null && $row['suggested_course'] !== null)
                    ->count(),
            ],
            [
                'label' => __('Graduation candidates'),
                'value' => $rows->filter(fn (array $row): bool => $row['is_graduation_candidate'])->count(),
            ],
        ];
    }

    /**
     * @return Collection<int, array{
     *     course: AcademicCourse,
     *     assigned_count: int
     * }>
     */
    public function courseSummaries(): Collection
    {
        $courseSelections = collect($this->normalizedCourseSelections())
            ->filter(fn (mixed $courseId): bool => $courseId !== null);

        return $this->academicCourses()
            ->map(function (AcademicCourse $course) use ($courseSelections): array {
                return [
                    'course' => $course,
                    'assigned_count' => $courseSelections
                        ->filter(fn (int $courseId): bool => $courseId === (int) $course->id)
                        ->count(),
                ];
            });
    }

    public function hasPendingChanges(): bool
    {
        return $this->normalizedCourseSelections() !== $this->normalizedOriginalCourseSelections();
    }

    public function updatedSearch(): void
    {
        $this->resetTable();
    }

    public function updatedAssignmentFilter(): void
    {
        $this->resetTable();
    }

    public function updatedClassFilterAcademicCourseId(): void
    {
        $this->resetTable();
    }

    public function updatedPreviousResultFilter(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->childUsersTableQuery())
            ->striped()
            ->columns([
                TextColumn::make('selection')
                    ->label(__('Select'))
                    ->state(fn (User $record): HtmlString => new HtmlString(
                        view('livewire.admin.arrangement.enrollments.columns.selection', [
                            'userId' => (int) $record->id,
                        ])->render()
                    ))
                    ->html(),
                TextColumn::make('christian_full_name')
                    ->label(__('Child'))
                    ->state(fn (User $record): string => $record->christian_full_name)
                    ->description(fn (User $record): string => (string) $record->username)
                    ->weight('medium')
                    ->wrap(),
                TextColumn::make('previous_class')
                    ->label(__('Previous class'))
                    ->state(fn (User $record): HtmlString => $this->previousClassHtml($record))
                    ->html()
                    ->wrap(),
                TextColumn::make('suggestion')
                    ->label(__('Suggestion'))
                    ->state(fn (User $record): HtmlString => $this->suggestionHtml($record))
                    ->html()
                    ->wrap(),
                TextColumn::make('current_class')
                    ->label(__('Current class'))
                    ->state(fn (User $record): HtmlString => $this->currentClassHtml($record))
                    ->html()
                    ->wrap(),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderByRaw(
                    'coalesce((select academic_courses.ordering from academic_courses where academic_courses.id = (select academic_enrollments.academic_course_id from academic_enrollments where academic_enrollments.user_id = users.id and academic_enrollments.academic_year_id = ? limit 1)), 99999)',
                    [$this->academicYearId],
                )
                ->orderBy('last_name')
                ->orderBy('name'))
            ->defaultPaginationPageOption(15)
            ->paginated([15, 25, 50, 100])
            ->extremePaginationLinks()
            ->stackedOnMobile()
            ->emptyStateHeading(__('No children match the current filters.'))
            ->emptyStateDescription(__('Try clearing the search or assignment filter to see more children.'))
            ->recordActions([
                ActionGroup::make([
                    Action::make('setClass')
                        ->label(__('Set class'))
                        ->icon('heroicon-m-pencil-square')
                        ->visible(fn (User $record): bool => $this->canUpdateAssignments() && ! $this->isCompletedProgram($this->previousEnrollmentFor($record)))
                        ->fillForm(fn (User $record): array => [
                            'academic_course_id' => $this->selectedCourseIdFor($record),
                        ])
                        ->schema([
                            Select::make('academic_course_id')
                                ->label(__('Current class'))
                                ->options($this->courseOptions())
                                ->searchable(),
                        ])
                        ->action(function (User $record, array $data): void {
                            $this->courseSelections[$record->id] = $data['academic_course_id'] ?? '';
                        }),
                    Action::make('applySuggestion')
                        ->label(__('Apply suggestion'))
                        ->icon('heroicon-m-arrow-trending-up')
                        ->color('success')
                        ->visible(function (User $record): bool {
                            return $this->canUpdateAssignments()
                                && ! $this->isCompletedProgram($this->previousEnrollmentFor($record))
                                && $this->suggestedCourseFor($this->previousEnrollmentFor($record), $this->academicCourses()->keyBy('ordering')) !== null;
                        })
                        ->action(function (User $record): void {
                            $suggestedCourse = $this->suggestedCourseFor(
                                $this->previousEnrollmentFor($record),
                                $this->academicCourses()->keyBy('ordering'),
                            );

                            if ($suggestedCourse !== null) {
                                $this->courseSelections[$record->id] = $suggestedCourse->id;
                            }
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
        return view('livewire.admin.arrangement.enrollments.enrollment-list', [
            'previousAcademicYear' => $this->previousAcademicYear(),
            'academicCourses' => $this->academicCourses(),
            'courseOptions' => $this->courseOptions(),
            'hasPendingChanges' => $this->hasPendingChanges(),
        ]);
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless($this->canUpdateAssignments(), 403);
    }

    protected function syncCourseSelections(): void
    {
        $currentEnrollments = AcademicEnrollment::query()
            ->where('academic_year_id', $this->academicYearId)
            ->whereIn('user_id', $this->childUserIds())
            ->pluck('academic_course_id', 'user_id');

        $this->courseSelections = collect($this->childUserIds())
            ->mapWithKeys(fn (int $userId): array => [
                $userId => $currentEnrollments->get($userId, ''),
            ])
            ->all();
        $this->originalCourseSelections = $this->courseSelections;
    }

    /**
     * @return array<int, int>
     */
    protected function childUserIds(): array
    {
        return User::query()
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', 'Thiếu Nhi');
            })
            ->pluck('id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->all();
    }

    /**
     * @return EloquentCollection<int, User>
     */
    protected function childUsers(): EloquentCollection
    {
        return $this->childUsersTableQuery()->get();
    }

    /**
     * @return Collection<int, array{
     *     user: User,
     *     current_enrollment: AcademicEnrollment|null,
     *     previous_enrollment: AcademicEnrollment|null,
     *     selected_course_id: int|null,
     *     selected_course: AcademicCourse|null,
     *     suggested_course: AcademicCourse|null,
     *     can_assign_class: bool,
     *     is_graduation_candidate: bool,
     *     recommendation_label: string,
     *     recommendation_color: string,
     *     previous_status_label: string
     * }>
     */
    protected function visibleRows(): Collection
    {
        $previousAcademicYearId = $this->previousAcademicYear()?->id;
        $academicCoursesById = $this->academicCourses()->keyBy('id');
        $academicCoursesByOrdering = $this->academicCourses()->keyBy('ordering');

        $rows = $this->childUsers()
            ->map(function (User $user) use ($previousAcademicYearId, $academicCoursesById, $academicCoursesByOrdering): array {
                /** @var AcademicEnrollment|null $currentEnrollment */
                $currentEnrollment = $user->academicEnrollments->firstWhere('academic_year_id', $this->academicYearId);
                /** @var AcademicEnrollment|null $previousEnrollment */
                $previousEnrollment = $previousAcademicYearId !== null
                    ? $user->academicEnrollments->firstWhere('academic_year_id', $previousAcademicYearId)
                    : null;

                $selectedCourseId = $this->normalizeNullableInt(
                    $this->courseSelections[$user->id] ?? $currentEnrollment?->academic_course_id,
                );
                /** @var AcademicCourse|null $selectedCourse */
                $selectedCourse = $selectedCourseId !== null ? $academicCoursesById->get($selectedCourseId) : null;
                $suggestedCourse = $this->suggestedCourseFor($previousEnrollment, $academicCoursesByOrdering);
                $isGraduationCandidate = $this->isCompletedProgram($previousEnrollment);

                return [
                    'user' => $user,
                    'current_enrollment' => $currentEnrollment,
                    'previous_enrollment' => $previousEnrollment,
                    'selected_course_id' => $selectedCourseId,
                    'selected_course' => $selectedCourse,
                    'suggested_course' => $suggestedCourse,
                    'can_assign_class' => ! $isGraduationCandidate,
                    'is_graduation_candidate' => $isGraduationCandidate,
                    'class_sort_order' => $this->rowClassOrdering($selectedCourse, $suggestedCourse, $previousEnrollment),
                    'class_sort_label' => $this->rowClassLabel($selectedCourse, $suggestedCourse, $previousEnrollment),
                    'recommendation_label' => $this->recommendationLabel($previousEnrollment, $suggestedCourse),
                    'recommendation_color' => $this->recommendationColor($previousEnrollment, $suggestedCourse),
                    'previous_status_label' => $this->previousStatusLabel($previousEnrollment),
                ];
            });

        $rows = match ($this->assignmentFilter) {
            'assigned' => $rows->filter(fn (array $row): bool => $row['selected_course_id'] !== null)->values(),
            'graduates' => $rows->filter(fn (array $row): bool => $row['is_graduation_candidate'])->values(),
            'unassigned' => $rows->filter(fn (array $row): bool => $row['selected_course_id'] === null)->values(),
            default => $rows->values(),
        };

        $rows = match ($this->previousResultFilter) {
            'pending_review' => $rows
                ->filter(fn (array $row): bool => $row['previous_enrollment'] !== null && ! $this->shouldPromote($row['previous_enrollment']))
                ->values(),
            'passed' => $rows
                ->filter(fn (array $row): bool => $row['previous_enrollment'] === null || $this->shouldPromote($row['previous_enrollment']))
                ->values(),
            default => $rows->values(),
        };

        $selectedClassFilterId = $this->normalizeNullableInt($this->classFilterAcademicCourseId);

        if ($selectedClassFilterId !== null) {
            $rows = $rows
                ->filter(fn (array $row): bool => $row['selected_course_id'] === $selectedClassFilterId)
                ->values();
        }

        return $rows
            ->sortBy(fn (array $row): string => sprintf(
                '%05d|%s|%s|%s',
                $row['class_sort_order'],
                mb_strtolower($row['class_sort_label']),
                mb_strtolower((string) $row['user']->last_name),
                mb_strtolower((string) $row['user']->name),
            ))
            ->values();
    }

    /**
     * @param  Collection<int|string, AcademicCourse>  $academicCoursesByOrdering
     */
    protected function suggestedCourseFor(?AcademicEnrollment $previousEnrollment, Collection $academicCoursesByOrdering): ?AcademicCourse
    {
        if ($previousEnrollment?->academicCourse === null) {
            return null;
        }

        $targetOrdering = $this->shouldPromote($previousEnrollment)
            ? ((int) $previousEnrollment->academicCourse->ordering + 1)
            : (int) $previousEnrollment->academicCourse->ordering;

        $course = $academicCoursesByOrdering->get($targetOrdering);

        return $course instanceof AcademicCourse ? $course : null;
    }

    protected function shouldPromote(AcademicEnrollment $previousEnrollment): bool
    {
        return $previousEnrollment->is_eligible_for_promotion === true
            || $previousEnrollment->status === 'passed';
    }

    protected function recommendationLabel(?AcademicEnrollment $previousEnrollment, ?AcademicCourse $suggestedCourse): string
    {
        if ($previousEnrollment === null) {
            return __('Assign manually');
        }

        if ($this->isCompletedProgram($previousEnrollment)) {
            return __('Graduate');
        }

        if ($suggestedCourse === null) {
            return __('Needs review');
        }

        return $this->shouldPromote($previousEnrollment)
            ? __('Promote')
            : __('Repeat class');
    }

    protected function recommendationColor(?AcademicEnrollment $previousEnrollment, ?AcademicCourse $suggestedCourse): string
    {
        if ($previousEnrollment === null) {
            return 'zinc';
        }

        if ($this->isCompletedProgram($previousEnrollment)) {
            return 'sky';
        }

        if ($suggestedCourse === null) {
            return 'amber';
        }

        return $this->shouldPromote($previousEnrollment)
            ? 'emerald'
            : 'amber';
    }

    protected function previousStatusLabel(?AcademicEnrollment $previousEnrollment): string
    {
        if ($previousEnrollment === null) {
            return __('No previous enrollment');
        }

        return match ($previousEnrollment->status) {
            'passed' => __('Passed'),
            'pending_review' => __('Pending review'),
            default => __('Studying'),
        };
    }

    protected function syncReligiousStudyStatus(
        int $userId,
        ?int $selectedCourseId,
        ?AcademicEnrollment $previousEnrollment,
        int $finalProgramOrdering,
    ): void {
        $statusReligious = $selectedCourseId === null && $this->isCompletedProgram($previousEnrollment, $finalProgramOrdering)
            ? 'graduated'
            : 'in_course';

        UserReligiousProfile::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'status_religious' => $statusReligious,
                'is_attendance' => true,
            ],
        );
    }

    protected function enrollmentHasRecordedData(AcademicEnrollment $academicEnrollment): bool
    {
        return $academicEnrollment->attendance_checkins_count > 0
            || $academicEnrollment->semester_scores_count > 0
            || $academicEnrollment->activity_points_count > 0
            || $academicEnrollment->promotion_review_count > 0
            || $academicEnrollment->final_catechism_score !== null
            || $academicEnrollment->final_conduct_score !== null
            || $academicEnrollment->final_activity_score !== null;
    }

    protected function normalizeNullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function rowClassOrdering(
        ?AcademicCourse $selectedCourse,
        ?AcademicCourse $suggestedCourse,
        ?AcademicEnrollment $previousEnrollment,
    ): int {
        if ($selectedCourse !== null) {
            return (int) $selectedCourse->ordering;
        }

        if ($suggestedCourse !== null) {
            return (int) $suggestedCourse->ordering;
        }

        return (int) ($previousEnrollment?->academicCourse?->ordering ?? PHP_INT_MAX);
    }

    protected function rowClassLabel(
        ?AcademicCourse $selectedCourse,
        ?AcademicCourse $suggestedCourse,
        ?AcademicEnrollment $previousEnrollment,
    ): string {
        if ($selectedCourse !== null) {
            return (string) $selectedCourse->course_name;
        }

        if ($suggestedCourse !== null) {
            return (string) $suggestedCourse->course_name;
        }

        return (string) ($previousEnrollment?->academicCourse?->course_name ?? 'zzz');
    }

    protected function isCompletedProgram(?AcademicEnrollment $previousEnrollment, ?int $finalProgramOrdering = null): bool
    {
        if ($previousEnrollment?->academicCourse === null) {
            return false;
        }

        $finalProgramOrdering ??= (int) $this->academicCourses()->max('ordering');

        return $this->shouldPromote($previousEnrollment)
            && (int) $previousEnrollment->academicCourse->ordering >= $finalProgramOrdering;
    }

    /**
     * @return array<int, int|null>
     */
    protected function normalizedCourseSelections(): array
    {
        return collect($this->courseSelections)
            ->mapWithKeys(fn (mixed $courseId, mixed $userId): array => [
                (int) $userId => $this->normalizeNullableInt($courseId),
            ])
            ->all();
    }

    /**
     * @return array<int, int|null>
     */
    protected function normalizedOriginalCourseSelections(): array
    {
        return collect($this->originalCourseSelections)
            ->mapWithKeys(fn (mixed $courseId, mixed $userId): array => [
                (int) $userId => $this->normalizeNullableInt($courseId),
            ])
            ->all();
    }

    protected function childUsersTableQuery(): Builder
    {
        $previousAcademicYearId = $this->previousAcademicYear()?->id;
        $relevantAcademicYearIds = collect([$this->academicYearId, $previousAcademicYearId])
            ->filter()
            ->map(fn (mixed $academicYearId): int => (int) $academicYearId)
            ->values()
            ->all();
        $selectedClassFilterId = $this->normalizeNullableInt($this->classFilterAcademicCourseId);
        $finalProgramOrdering = (int) $this->academicCourses()->max('ordering');

        return User::query()
            ->with([
                'academicEnrollments' => fn ($query) => $query
                    ->whereIn('academic_year_id', $relevantAcademicYearIds)
                    ->with('academicCourse:id,course_name,sector_name,ordering')
                    ->orderBy('academic_year_id'),
                'religious_profile:user_id,status_religious',
            ])
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', 'Thiếu Nhi');
            })
            ->when($this->search !== '', function (Builder $query): void {
                $search = '%'.$this->search.'%';

                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('last_name', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhere('christian_name', 'like', $search)
                        ->orWhere('username', 'like', $search);
                });
            })
            ->when($selectedClassFilterId !== null, function (Builder $query) use ($selectedClassFilterId): void {
                $query->whereHas('academicEnrollments', function (Builder $enrollmentQuery) use ($selectedClassFilterId): void {
                    $enrollmentQuery
                        ->where('academic_year_id', $this->academicYearId)
                        ->where('academic_course_id', $selectedClassFilterId);
                });
            })
            ->when($this->assignmentFilter === 'assigned', function (Builder $query): void {
                $query->whereHas('academicEnrollments', function (Builder $enrollmentQuery): void {
                    $enrollmentQuery->where('academic_year_id', $this->academicYearId);
                });
            })
            ->when($this->assignmentFilter === 'unassigned', function (Builder $query): void {
                $query->whereDoesntHave('academicEnrollments', function (Builder $enrollmentQuery): void {
                    $enrollmentQuery->where('academic_year_id', $this->academicYearId);
                });
            })
            ->when($this->assignmentFilter === 'graduates', function (Builder $query) use ($previousAcademicYearId, $finalProgramOrdering): void {
                if ($previousAcademicYearId === null || $finalProgramOrdering === 0) {
                    $query->whereRaw('0 = 1');

                    return;
                }

                $query->whereHas('academicEnrollments', function (Builder $enrollmentQuery) use ($previousAcademicYearId, $finalProgramOrdering): void {
                    $enrollmentQuery
                        ->where('academic_year_id', $previousAcademicYearId)
                        ->where(function (Builder $promotionQuery): void {
                            $promotionQuery
                                ->where('is_eligible_for_promotion', true)
                                ->orWhere('status', 'passed');
                        })
                        ->whereHas('academicCourse', function (Builder $courseQuery) use ($finalProgramOrdering): void {
                            $courseQuery->where('ordering', '>=', $finalProgramOrdering);
                        });
                });
            })
            ->when($this->previousResultFilter === 'pending_review', function (Builder $query) use ($previousAcademicYearId): void {
                if ($previousAcademicYearId === null) {
                    $query->whereRaw('0 = 1');

                    return;
                }

                $query->whereHas('academicEnrollments', function (Builder $enrollmentQuery) use ($previousAcademicYearId): void {
                    $enrollmentQuery
                        ->where('academic_year_id', $previousAcademicYearId)
                        ->where(function (Builder $promotionQuery): void {
                            $promotionQuery
                                ->where('is_eligible_for_promotion', false)
                                ->orWhere('status', 'pending_review');
                        });
                });
            })
            ->when($this->previousResultFilter === 'passed', function (Builder $query) use ($previousAcademicYearId): void {
                if ($previousAcademicYearId === null) {
                    return;
                }

                $query->where(function (Builder $filterQuery) use ($previousAcademicYearId): void {
                    $filterQuery
                        ->whereDoesntHave('academicEnrollments', function (Builder $enrollmentQuery) use ($previousAcademicYearId): void {
                            $enrollmentQuery->where('academic_year_id', $previousAcademicYearId);
                        })
                        ->orWhereHas('academicEnrollments', function (Builder $enrollmentQuery) use ($previousAcademicYearId): void {
                            $enrollmentQuery
                                ->where('academic_year_id', $previousAcademicYearId)
                                ->where(function (Builder $promotionQuery): void {
                                    $promotionQuery
                                        ->where('is_eligible_for_promotion', true)
                                        ->orWhere('status', 'passed');
                                });
                        });
                });
            });
    }

    protected function currentEnrollmentFor(User $user): ?AcademicEnrollment
    {
        return $user->academicEnrollments->firstWhere('academic_year_id', $this->academicYearId);
    }

    protected function previousEnrollmentFor(User $user): ?AcademicEnrollment
    {
        $previousAcademicYearId = $this->previousAcademicYear()?->id;

        if ($previousAcademicYearId === null) {
            return null;
        }

        return $user->academicEnrollments->firstWhere('academic_year_id', $previousAcademicYearId);
    }

    protected function selectedCourseIdFor(User $user): ?int
    {
        return $this->normalizeNullableInt(
            $this->courseSelections[$user->id] ?? $this->currentEnrollmentFor($user)?->academic_course_id,
        );
    }

    protected function previousClassHtml(User $user): HtmlString
    {
        $previousEnrollment = $this->previousEnrollmentFor($user);

        if ($previousEnrollment?->academicCourse === null) {
            return new HtmlString('<span class="text-sm text-zinc-500 dark:text-zinc-400">'.e(__('No previous enrollment')).'</span>');
        }

        return new HtmlString(implode('', [
            '<div class="space-y-2">',
            '<div><div class="font-medium text-zinc-950 dark:text-zinc-50">'.e($previousEnrollment->academicCourse->course_name).'</div>',
            '<div class="text-sm text-zinc-600 dark:text-zinc-400">'.e($previousEnrollment->academicCourse->sector_name).'</div></div>',
            '<span class="inline-flex rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">'.e($this->previousStatusLabel($previousEnrollment)).'</span>',
            '</div>',
        ]));
    }

    protected function suggestionHtml(User $user): HtmlString
    {
        $previousEnrollment = $this->previousEnrollmentFor($user);
        $suggestedCourse = $this->suggestedCourseFor($previousEnrollment, $this->academicCourses()->keyBy('ordering'));
        $badgeColorClasses = match ($this->recommendationColor($previousEnrollment, $suggestedCourse)) {
            'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
            'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
            'sky' => 'bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
            default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
        };

        $parts = [
            '<div class="space-y-2">',
            '<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium '.$badgeColorClasses.'">'.e($this->recommendationLabel($previousEnrollment, $suggestedCourse)).'</span>',
        ];

        if ($suggestedCourse !== null) {
            $parts[] = '<div><div class="font-medium text-zinc-950 dark:text-zinc-50">'.e($suggestedCourse->course_name).'</div>';
            $parts[] = '<div class="text-sm text-zinc-600 dark:text-zinc-400">'.e($suggestedCourse->sector_name).'</div></div>';
        } elseif ($this->isCompletedProgram($previousEnrollment)) {
            $parts[] = '<div class="text-sm text-zinc-500 dark:text-zinc-400">'.e(__('No next class is available for this child.')).'</div>';
        } else {
            $parts[] = '<div class="text-sm text-zinc-500 dark:text-zinc-400">'.e(__('Choose a class manually for this child.')).'</div>';
        }

        $parts[] = '</div>';

        return new HtmlString(implode('', $parts));
    }

    protected function currentClassHtml(User $user): HtmlString
    {
        $previousEnrollment = $this->previousEnrollmentFor($user);

        if ($this->isCompletedProgram($previousEnrollment)) {
            return new HtmlString(implode('', [
                '<div class="rounded-xl border border-sky-200 bg-sky-50 p-3 dark:border-sky-900/60 dark:bg-sky-950/30">',
                '<div class="font-medium text-sky-800 dark:text-sky-300">'.e(__('Graduation status')).'</div>',
                '<div class="mt-2 text-sm text-sky-700 dark:text-sky-400">'.e(__('This child has completed the final class and will be marked as graduated when you save.')).'</div>',
                '</div>',
            ]));
        }

        $selectedCourseId = $this->selectedCourseIdFor($user);
        $selectedCourse = $selectedCourseId !== null ? $this->academicCourses()->firstWhere('id', $selectedCourseId) : null;

        if ($selectedCourse === null) {
            return new HtmlString('<span class="text-sm text-zinc-500 dark:text-zinc-400">'.e(__('No class selected')).'</span>');
        }

        return new HtmlString(implode('', [
            '<div class="space-y-1">',
            '<div class="font-medium text-zinc-950 dark:text-zinc-50">'.e($selectedCourse->course_name).'</div>',
            '<div class="text-sm text-zinc-600 dark:text-zinc-400">'.e($selectedCourse->sector_name).'</div>',
            '</div>',
        ]));
    }
}
