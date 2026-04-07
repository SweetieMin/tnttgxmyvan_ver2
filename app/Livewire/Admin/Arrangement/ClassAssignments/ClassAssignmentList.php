<?php

namespace App\Livewire\Admin\Arrangement\ClassAssignments;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ClassAssignmentList extends Component
{
    public int $academicYearId;

    /**
     * @var Collection<int, AcademicCourse>|null
     */
    protected ?Collection $classAssignmentsCache = null;

    #[On('class-assignment-saved')]
    public function refreshAssignments(): void
    {
        $this->classAssignmentsCache = null;
    }

    /**
     * @return Collection<int, AcademicCourse>
     */
    public function classAssignments(): Collection
    {
        return $this->classAssignmentsCache ??= AcademicCourse::query()
            ->with([
                'staffAssignments' => fn ($query) => $query
                    ->whereIn('assignment_type', ['catechist', 'assistant_catechist'])
                    ->with('user:id,last_name,name'),
            ])
            ->where('academic_year_id', $this->academicYearId)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function overviewStats(): array
    {
        $classAssignments = $this->classAssignments();

        return [
            [
                'label' => __('Catechism classes'),
                'value' => $classAssignments->count(),
            ],
            [
                'label' => __('Classes with primary catechists'),
                'value' => $classAssignments
                    ->filter(fn (AcademicCourse $course): bool => $this->primaryCatechistName($course) !== null)
                    ->count(),
            ],
            [
                'label' => __('Classes missing catechists'),
                'value' => $classAssignments
                    ->filter(fn (AcademicCourse $course): bool => $this->primaryCatechistName($course) === null && $this->supportingCatechistNames($course) === [])
                    ->count(),
            ],
        ];
    }

    public function editClassAssignment(int $academicCourseId): void
    {
        if (! $this->canUpdateAssignments()) {
            return;
        }

        $this->dispatch('edit-class-catechist-assignment', academicCourseId: $academicCourseId);
    }

    public function canUpdateAssignments(): bool
    {
        return Auth::user()?->can('arrangement.class-assignment.update') ?? false;
    }

    public function academicYear(): ?AcademicYear
    {
        return AcademicYear::query()->find($this->academicYearId);
    }

    public function primaryCatechistName(AcademicCourse $course): ?string
    {
        return $course->staffAssignments
            ->firstWhere('assignment_type', 'catechist')
            ?->user
            ?->full_name;
    }

    /**
     * @return array<int, string>
     */
    public function supportingCatechistNames(AcademicCourse $course): array
    {
        return $course->staffAssignments
            ->where('assignment_type', 'assistant_catechist')
            ->pluck('user.full_name')
            ->filter()
            ->sort()
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.admin.arrangement.class-assignments.class-assignment-list', [
            'academicYear' => $this->academicYear(),
            'classAssignments' => $this->classAssignments(),
            'overviewStats' => $this->overviewStats(),
        ]);
    }
}
