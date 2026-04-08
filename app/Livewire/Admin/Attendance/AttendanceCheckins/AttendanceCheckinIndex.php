<?php

namespace App\Livewire\Admin\Attendance\AttendanceCheckins;

use App\Models\AttendanceSchedule;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Điểm danh sinh hoạt')]
class AttendanceCheckinIndex extends Component
{
    public ?int $attendanceScheduleId = null;

    public function mount(): void
    {
        $this->expireFinishedSchedules();
        $this->attendanceScheduleId = $this->detectCurrentScheduleId();
    }

    public function render(): View
    {
        return view('livewire.admin.attendance.attendance-checkins.index');
    }

    protected function detectCurrentScheduleId(): ?int
    {
        $now = now()->timezone('Asia/Ho_Chi_Minh');

        return AttendanceSchedule::query()
            ->whereDate('attendance_date', $now->toDateString())
            ->where('is_active', true)
            ->where('start_time', '<=', $now->format('H:i:s'))
            ->where('end_time', '>=', $now->format('H:i:s'))
            ->latest('start_time')
            ->value('id');
    }

    protected function expireFinishedSchedules(): void
    {
        $now = now()->timezone('Asia/Ho_Chi_Minh');

        AttendanceSchedule::query()
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereDate('attendance_date', '<', $now->toDateString())
                    ->orWhere(function ($nestedQuery) use ($now) {
                        $nestedQuery
                            ->whereDate('attendance_date', $now->toDateString())
                            ->where('end_time', '<', $now->format('H:i:s'));
                    });
            })
            ->update(['is_active' => false]);
    }
}
