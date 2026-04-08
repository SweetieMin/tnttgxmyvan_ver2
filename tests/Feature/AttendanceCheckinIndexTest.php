<?php

use App\Livewire\Admin\Attendance\AttendanceCheckins\AttendanceCheckinIndex;
use App\Models\AcademicYear;
use App\Models\AttendanceSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 8, 7, 30, 0, 'Asia/Ho_Chi_Minh'));
});

afterEach(function () {
    Carbon::setTestNow();
});

test('attendance checkin index resolves the active schedule during mount', function () {
    $academicYear = AcademicYear::factory()->create();

    $schedule = AttendanceSchedule::factory()->create([
        'academic_year_id' => $academicYear->id,
        'attendance_date' => '2026-04-08',
        'start_time' => '07:00:00',
        'end_time' => '08:00:00',
        'is_active' => true,
    ]);

    $component = app(AttendanceCheckinIndex::class);
    $component->mount();

    expect($component->attendanceScheduleId)->toBe($schedule->id);
});

test('attendance checkin index keeps the schedule empty when no session is active', function () {
    AttendanceSchedule::factory()->create([
        'attendance_date' => '2026-04-08',
        'start_time' => '08:30:00',
        'end_time' => '09:30:00',
        'is_active' => true,
    ]);

    $component = app(AttendanceCheckinIndex::class);
    $component->mount();

    expect($component->attendanceScheduleId)->toBeNull();
});
