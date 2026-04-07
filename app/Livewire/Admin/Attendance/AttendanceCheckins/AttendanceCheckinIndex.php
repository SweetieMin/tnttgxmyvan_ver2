<?php

namespace App\Livewire\Admin\Attendance\AttendanceCheckins;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm danh sinh hoạt')]
class AttendanceCheckinIndex extends Component
{
    public ?int $attendanceScheduleId = null;

    public function render(): View
    {
        return view('livewire.admin.attendance.attendance-checkins.index');
    }
}
