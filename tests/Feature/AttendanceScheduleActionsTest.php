<?php

use App\Livewire\Admin\Management\AttendanceSchedules\AttendanceScheduleActions;
use App\Models\AcademicYear;
use App\Models\AttendanceSchedule;
use App\Models\Permission;
use App\Models\Regulation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    collect([
        'management.attendance-schedule.view',
        'management.attendance-schedule.create',
        'management.attendance-schedule.update',
        'management.attendance-schedule.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('attendance schedule actions prefill the generated title and regulation short description', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.attendance-schedule.create');

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $regulation = Regulation::factory()->create([
        'short_desc' => 'Lễ CN',
        'description' => 'Tham dự Thánh Lễ Chúa Nhật',
        'type' => 'plus',
        'status' => 'pending',
        'points' => 10,
    ]);

    Regulation::factory()->create([
        'short_desc' => 'Trừ điểm',
        'type' => 'minus',
    ]);

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleActions::class)
        ->call('openCreateModal', '2026-04-19', $academicYear->id)
        ->assertSet('attendance_date', '2026-04-19')
        ->assertSet('title', '19/04/26')
        ->assertSet('academic_year_id', $academicYear->id)
        ->set('regulation_id', $regulation->id)
        ->call('selectedRegulationShortDescription')
        ->assertReturned('Lễ CN')
        ->call('selectedRegulationDescription')
        ->assertReturned('Tham dự Thánh Lễ Chúa Nhật')
        ->assertSet('title', 'Lễ CN 19/04/26')
        ->assertSet('points', 10);

    Livewire::test(AttendanceScheduleActions::class)
        ->call('regulations')
        ->assertReturned(
            fn ($regulations) => count($regulations) === 1
                && (int) $regulations[0]['id'] === $regulation->id
                && $regulations[0]['description'] === 'Tham dự Thánh Lễ Chúa Nhật'
                && $regulations[0]['short_desc'] === 'Lễ CN'
        );
});

test('attendance schedules can be created updated and deleted from the actions component', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.attendance-schedule.create',
        'management.attendance-schedule.update',
        'management.attendance-schedule.delete',
    ]);

    $academicYear = AcademicYear::factory()->create();
    $regulation = Regulation::factory()->create([
        'short_desc' => 'Lễ CN',
        'description' => 'Tham dự Thánh Lễ Chúa Nhật',
        'type' => 'plus',
        'points' => 10,
    ]);

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleActions::class)
        ->call('openCreateModal', '2026-04-19', $academicYear->id)
        ->set('regulation_id', $regulation->id)
        ->set('start_time', '07:00')
        ->set('end_time', '08:30')
        ->set('points', 10)
        ->call('saveAttendanceSchedule')
        ->assertHasNoErrors();

    $attendanceSchedule = AttendanceSchedule::query()->firstOrFail();

    expect($attendanceSchedule->title)->toBe('Lễ CN 19/04/26')
        ->and($attendanceSchedule->regulation_id)->toBe($regulation->id);

    Livewire::test(AttendanceScheduleActions::class)
        ->call('openEditModal', $attendanceSchedule->id)
        ->set('attendance_date', '2026-04-20')
        ->set('start_time', '18:00')
        ->set('end_time', '19:15')
        ->call('saveAttendanceSchedule')
        ->assertHasNoErrors();

    expect($attendanceSchedule->fresh()->title)->toBe('Lễ CN 20/04/26')
        ->and($attendanceSchedule->fresh()->start_time)->toContain('18:00')
        ->and($attendanceSchedule->fresh()->end_time)->toContain('19:15');

    Livewire::test(AttendanceScheduleActions::class)
        ->call('openEditModal', $attendanceSchedule->id)
        ->call('confirmDeleteAttendanceSchedule')
        ->call('deleteAttendanceSchedule')
        ->assertHasNoErrors();

    expect(AttendanceSchedule::query()->whereKey($attendanceSchedule->id)->exists())->toBeFalse();
});

test('attendance schedule requires a regulation selection', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.attendance-schedule.create');

    $academicYear = AcademicYear::factory()->create();

    $this->actingAs($user);

    Livewire::test(AttendanceScheduleActions::class)
        ->call('openCreateModal', '2026-04-19', $academicYear->id)
        ->set('start_time', '07:00')
        ->set('end_time', '08:30')
        ->set('points', 0)
        ->call('saveAttendanceSchedule')
        ->assertHasErrors(['regulation_id' => 'required']);
});
