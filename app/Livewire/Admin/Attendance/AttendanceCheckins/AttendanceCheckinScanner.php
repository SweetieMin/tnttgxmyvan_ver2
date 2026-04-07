<?php

namespace App\Livewire\Admin\Attendance\AttendanceCheckins;

use App\Models\AcademicEnrollment;
use App\Models\AttendanceCheckin;
use App\Models\AttendanceSchedule;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AttendanceCheckinScanner extends Component
{
    public ?int $attendanceScheduleId = null;

    public bool $cameraActive = false;

    /** @var array<string, mixed>|null */
    public ?array $lastScannedUser = null;

    public string $lastScanStatus = '';

    public function mount(): void
    {
        $this->detectCurrentSchedule();
    }

    public function detectCurrentSchedule(): void
    {
        /** @var AttendanceSchedule|null $schedule */
        $schedule = AttendanceSchedule::query()
            ->where('attendance_date', today())
            ->where('is_active', true)
            ->whereTime('start_time', '<=', now())
            ->whereTime('end_time', '>=', now())
            ->latest('start_time')
            ->first();

        $this->attendanceScheduleId = $schedule?->id;

        if ($this->attendanceScheduleId) {
            $this->dispatch('schedule-changed', scheduleId: (int) $this->attendanceScheduleId);
        }

        $this->cameraActive = false;
        $this->lastScannedUser = null;
        $this->lastScanStatus = '';
    }

    public function currentSchedule(): ?AttendanceSchedule
    {
        if (! $this->attendanceScheduleId) {
            return null;
        }

        return AttendanceSchedule::query()->find($this->attendanceScheduleId);
    }

    public function toggleCamera(): void
    {
        if (! $this->attendanceScheduleId) {
            Flux::toast(
                text: __('No active schedule found for this time slot.'),
                variant: 'warning',
            );

            return;
        }

        $this->cameraActive = ! $this->cameraActive;
        $this->lastScannedUser = null;
        $this->lastScanStatus = '';
    }

    public function processQrCode(string $qrValue): void
    {
        if (! $this->attendanceScheduleId) {
            return;
        }

        /** @var AttendanceSchedule|null $schedule */
        $schedule = AttendanceSchedule::query()->find($this->attendanceScheduleId);

        if ($schedule === null) {
            $this->lastScanStatus = 'error';
            $this->lastScannedUser = null;

            return;
        }

        // Extract token from profile URL or raw token
        $token = $this->extractToken($qrValue);

        /** @var User|null $user */
        $user = User::query()->where('token', $token)->first();

        if ($user === null) {
            $this->lastScanStatus = 'not_found';
            $this->lastScannedUser = null;

            return;
        }

        /** @var AcademicEnrollment|null $enrollment */
        $enrollment = AcademicEnrollment::query()
            ->where('user_id', $user->id)
            ->where('academic_year_id', $schedule->academic_year_id)
            ->first();

        if ($enrollment === null) {
            $this->lastScanStatus = 'not_enrolled';
            $this->lastScannedUser = ['name' => $user->christian_full_name, 'id' => $user->id];

            return;
        }

        $alreadyCheckedIn = AttendanceCheckin::query()
            ->where('attendance_schedule_id', $this->attendanceScheduleId)
            ->where('academic_enrollment_id', $enrollment->id)
            ->exists();

        if ($alreadyCheckedIn) {
            $this->lastScanStatus = 'already';
            $this->lastScannedUser = ['name' => $user->christian_full_name, 'id' => $user->id];

            return;
        }

        AttendanceCheckin::query()->create([
            'attendance_schedule_id' => (int) $this->attendanceScheduleId,
            'academic_enrollment_id' => (int) $enrollment->id,
            'checked_in_at' => now(),
            'checkin_method' => 'qr',
            'recorded_by' => Auth::id(),
            'status' => 'pending',
        ]);

        $this->lastScanStatus = 'success';
        $this->lastScannedUser = ['name' => $user->christian_full_name, 'id' => $user->id];

        $this->dispatch('checkin-recorded');
    }

    public function render(): View
    {
        return view('livewire.admin.attendance.attendance-checkins.attendance-checkin-scanner');
    }

    protected function extractToken(string $qrValue): string
    {
        // If looks like a URL, extract last path segment
        if (str_contains($qrValue, '/')) {
            return trim((string) last(explode('/', rtrim($qrValue, '/'))));
        }

        return trim($qrValue);
    }
}
