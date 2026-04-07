<?php

use App\Livewire\Admin\Arrangement\AttendanceSchedules\AttendanceScheduleCalendar;
use App\Models\AcademicYear;
use App\Models\AttendanceSchedule;
use App\Models\Permission;
use App\Models\Regulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Livewire::withoutLazyLoading();

    collect([
        'arrangement.attendance-schedule.view',
        'arrangement.attendance-schedule.create',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('attendance schedule calendar shows schedules from the current calendar grid', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.attendance-schedule.view');

    $regulation = Regulation::factory()->create([
        'description' => 'Lễ Chúa Nhật',
    ]);

    AttendanceSchedule::factory()->create([
        'title' => 'Lễ CN tuần 1',
        'attendance_date' => now()->startOfMonth()->addDays(4)->toDateString(),
        'regulation_id' => $regulation->id,
        'points' => 10,
    ]);

    AttendanceSchedule::factory()->create([
        'title' => 'Lễ CN tương lai',
        'attendance_date' => now()->addMonths(3)->startOfMonth()->toDateString(),
        'regulation_id' => $regulation->id,
        'points' => 8,
    ]);

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleCalendar::class, [
        'gridStartsAt' => now()->startOfMonth()->startOfWeek(),
        'gridEndsAt' => now()->endOfMonth()->endOfWeek(),
    ])
        ->assertSee('Lễ CN tuần 1')
        ->assertDontSee('Lễ CN tương lai');
});

test('attendance schedule calendar maps regulation keywords to event colors', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.attendance-schedule.view');

    $holyMassRegulation = Regulation::factory()->create([
        'short_desc' => 'Tham dự Thánh Lễ',
        'description' => 'Tham dự Thánh Lễ Chúa Nhật',
        'type' => 'plus',
        'status' => 'applied',
    ]);

    $adorationRegulation = Regulation::factory()->create([
        'short_desc' => 'Tham dự giờ chầu',
        'description' => 'Tham dự các giờ Chầu Thánh Thể',
        'type' => 'plus',
        'status' => 'applied',
    ]);

    $holyMassSchedule = AttendanceSchedule::factory()->create([
        'title' => 'Lễ CN',
        'attendance_date' => now()->startOfMonth()->addDays(2)->toDateString(),
        'regulation_id' => $holyMassRegulation->id,
    ]);

    $adorationSchedule = AttendanceSchedule::factory()->create([
        'title' => 'Giờ chầu',
        'attendance_date' => now()->startOfMonth()->addDays(3)->toDateString(),
        'regulation_id' => $adorationRegulation->id,
    ]);

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleCalendar::class, [
        'gridStartsAt' => now()->startOfMonth()->startOfWeek(),
        'gridEndsAt' => now()->endOfMonth()->endOfWeek(),
    ])
        ->call('events')
        ->assertReturned(fn (array $events) => collect($events)->contains(
            fn (array $event): bool => $event['id'] === $holyMassSchedule->id
                && $event['dot_class'] === 'bg-red-500'
        ) && collect($events)->contains(
            fn (array $event): bool => $event['id'] === $adorationSchedule->id
                && $event['dot_class'] === 'bg-amber-500'
        ));
});

test('attendance schedules page renders the package calendar component', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.attendance-schedule.view',
        'arrangement.attendance-schedule.create',
    ]);

    $this->actingAs($user)
        ->get(route('admin.arrangement.attendance-schedules'))
        ->assertOk()
        ->assertSee(__('Attendance schedules'))
        ->assertSee(__('Add attendance schedule'));
});

test('clicking a day prepares the attendance schedule modal', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.attendance-schedule.view',
        'arrangement.attendance-schedule.create',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleCalendar::class, [
        'gridStartsAt' => now()->startOfMonth()->startOfWeek(),
        'gridEndsAt' => now()->endOfMonth()->endOfWeek(),
    ])
        ->call('onDayClick', 2026, 4, 18)
        ->assertDispatched('open-create-attendance-schedule-modal');
});

test('attendance schedule event click loads data and dropped event updates the date', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.attendance-schedule.view');

    $academicYear = AcademicYear::factory()->create();
    $regulation = Regulation::factory()->create([
        'description' => 'Lễ thứ Năm',
        'status' => 'applied',
        'type' => 'plus',
    ]);

    $attendanceSchedule = AttendanceSchedule::factory()->create([
        'academic_year_id' => $academicYear->id,
        'title' => 'Lễ thứ 5',
        'attendance_date' => '2026-04-09',
        'start_time' => '18:30:00',
        'end_time' => '19:30:00',
        'regulation_id' => $regulation->id,
        'points' => 8,
    ]);

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleCalendar::class, [
        'gridStartsAt' => now()->startOfMonth()->startOfWeek(),
        'gridEndsAt' => now()->endOfMonth()->endOfWeek(),
    ])
        ->call('onEventClick', $attendanceSchedule->id)
        ->assertDispatched('edit-attendance-schedule')
        ->call('onEventDropped', $attendanceSchedule->id, 2026, 4, 12);

    expect($attendanceSchedule->refresh()->attendance_date->toDateString())->toBe('2026-04-12');
});
