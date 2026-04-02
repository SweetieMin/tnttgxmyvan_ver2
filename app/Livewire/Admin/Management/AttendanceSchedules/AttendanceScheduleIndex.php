<?php

namespace App\Livewire\Admin\Management\AttendanceSchedules;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lịch điểm danh')]
class AttendanceScheduleIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.management.attendance-schedules.index');
    }
}
