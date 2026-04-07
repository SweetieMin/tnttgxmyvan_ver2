<?php

namespace App\Livewire\Admin\Arrangement\AttendanceSchedules;

use App\Models\AcademicYear;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lịch điểm danh')]
class AttendanceScheduleIndex extends Component
{
    public function openCreateModal(): void
    {
        $this->dispatch(
            'open-create-attendance-schedule-modal',
            attendanceDate: now()->toDateString(),
            academicYearId: $this->defaultAcademicYearId(),
        );
    }

    public function render(): View
    {
        return view('livewire.admin.arrangement.attendance-schedules.index');
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
