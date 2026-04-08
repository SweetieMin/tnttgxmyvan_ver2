<?php

use App\Livewire\Admin\Attendance\AttendanceCheckins\AttendanceCheckinScanner;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\AttendanceCheckin;
use App\Models\AttendanceSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::create(2026, 4, 8, 7, 30, 0, 'Asia/Ho_Chi_Minh'));
});

afterEach(function () {
    Carbon::setTestNow();
});

test('attendance scanner records a check-in from a profile qr url', function () {
    $operator = User::factory()->create();
    $member = User::factory()->create([
        'token' => 'member-qr-token',
    ]);

    $academicYear = AcademicYear::factory()->create();

    $schedule = AttendanceSchedule::factory()->create([
        'academic_year_id' => $academicYear->id,
        'attendance_date' => now('Asia/Ho_Chi_Minh')->toDateString(),
        'start_time' => now('Asia/Ho_Chi_Minh')->subMinutes(30)->format('H:i:s'),
        'end_time' => now('Asia/Ho_Chi_Minh')->addMinutes(30)->format('H:i:s'),
        'is_active' => true,
    ]);

    $enrollment = AcademicEnrollment::factory()->create([
        'user_id' => $member->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $this->actingAs($operator);

    Livewire::test(AttendanceCheckinScanner::class)
        ->set('attendanceScheduleId', $schedule->id)
        ->call('processQrCode', route('front.profile.show', ['token' => $member->token]))
        ->assertDispatched('checkin-recorded');

    $checkin = AttendanceCheckin::query()->first();

    expect($checkin)
        ->not->toBeNull()
        ->and($checkin?->attendance_schedule_id)->toBe($schedule->id)
        ->and($checkin?->academic_enrollment_id)->toBe($enrollment->id)
        ->and($checkin?->checkin_method)->toBe('qr')
        ->and($checkin?->recorded_by)->toBe($operator->id)
        ->and($checkin?->status)->toBe('pending');
});

test('attendance scanner keeps repeated scans idempotent', function () {
    $operator = User::factory()->create();
    $member = User::factory()->create([
        'token' => 'member-repeat-token',
    ]);

    $academicYear = AcademicYear::factory()->create();

    $schedule = AttendanceSchedule::factory()->create([
        'academic_year_id' => $academicYear->id,
        'attendance_date' => now('Asia/Ho_Chi_Minh')->toDateString(),
        'start_time' => now('Asia/Ho_Chi_Minh')->subMinutes(30)->format('H:i:s'),
        'end_time' => now('Asia/Ho_Chi_Minh')->addMinutes(30)->format('H:i:s'),
        'is_active' => true,
    ]);

    $enrollment = AcademicEnrollment::factory()->create([
        'user_id' => $member->id,
        'academic_year_id' => $academicYear->id,
    ]);

    AttendanceCheckin::factory()->create([
        'attendance_schedule_id' => $schedule->id,
        'academic_enrollment_id' => $enrollment->id,
        'recorded_by' => $operator->id,
    ]);

    $this->actingAs($operator);

    Livewire::test(AttendanceCheckinScanner::class)
        ->set('attendanceScheduleId', $schedule->id)
        ->call('processQrCode', $member->token);

    expect(AttendanceCheckin::query()->count())->toBe(1);
});
