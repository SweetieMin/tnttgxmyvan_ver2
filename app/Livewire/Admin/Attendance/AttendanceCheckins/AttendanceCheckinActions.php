<?php

namespace App\Livewire\Admin\Attendance\AttendanceCheckins;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceCheckinActions extends Component
{
    public ?int $attendanceScheduleId = null;

    public function mount(?int $attendanceScheduleId = null): void
    {
        $this->attendanceScheduleId = $attendanceScheduleId;
    }

    public function render(): View
    {
        return view('livewire.admin.attendance.attendance-checkins.attendance-checkin-actions');
    }
}
