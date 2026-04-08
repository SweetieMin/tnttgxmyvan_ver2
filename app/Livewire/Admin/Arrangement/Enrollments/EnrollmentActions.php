<?php

namespace App\Livewire\Admin\Arrangement\Enrollments;

use App\Models\AcademicCourse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class EnrollmentActions extends Component
{
    public int $academicYearId;

    public function mount(int $academicYearId): void
    {
        $this->academicYearId = $academicYearId;
    }

    /**
     * @return Collection<int, array{course: AcademicCourse, assigned_count: int}>
     */
    public function courseSummaries(): Collection
    {
        return AcademicCourse::query()
            ->withCount([
                'enrollments as assigned_count' => fn ($query) => $query->where('academic_year_id', $this->academicYearId),
            ])
            ->where('academic_year_id', $this->academicYearId)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get()
            ->map(fn (AcademicCourse $course): array => [
                'course' => $course,
                'assigned_count' => (int) $course->assigned_count,
            ]);
    }

    public function render(): View
    {
        return view('livewire.admin.arrangement.enrollments.enrollment-actions', [
            'courseSummaries' => $this->courseSummaries(),
        ]);
    }
}
