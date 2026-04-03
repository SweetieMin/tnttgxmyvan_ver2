<?php

namespace App\Livewire\Admin\Management\AcademicCourses;

use App\Models\AcademicYear;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lớp giáo lý - ngành')]
class AcademicCourseIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch('open-create-academic-course-modal', academicYearId: $this->defaultAcademicYearId());
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-courses.index');
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
