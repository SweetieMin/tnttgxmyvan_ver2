<?php

use App\Livewire\Admin\Arrangement\SectorAssignments\SectorAssignmentActions;
use App\Livewire\Admin\Arrangement\SectorAssignments\SectorAssignmentList;
use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\AcademicYearSectorStaff;
use App\Models\Permission;
use App\Models\PersonnelRoleGroup;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    collect([
        'arrangement.sector-assignment.view',
        'arrangement.sector-assignment.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

function sectorAssignmentPersonnelRole(string $roleName): Role
{
    $role = Role::findOrCreate($roleName, 'web');
    $groupKeys = match ($roleName) {
        'Xứ Đoàn Trưởng', 'Xứ Đoàn Phó', 'Huynh Trưởng', 'Dự Trưởng', 'Trưởng Ngành Thiếu', 'Phó Ngành Thiếu' => ['leaders'],
        default => [],
    };

    if ($groupKeys !== []) {
        PersonnelRoleGroup::query()->where('role_id', $role->id)->delete();

        PersonnelRoleGroup::query()->insert(
            collect($groupKeys)
                ->map(fn (string $groupKey): array => [
                    'role_id' => $role->id,
                    'group_key' => $groupKey,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all(),
        );
    }

    return $role;
}

test('authorized users can view the sector assignment overview', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.sector-assignment.view');

    $academicYear = AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Khai Tâm 1',
        'sector_name' => 'Ấu 1',
    ]);

    $this->actingAs($user)
        ->get(route('admin.arrangement.sector-assignments'))
        ->assertOk()
        ->assertSeeText(__('Sector leader assignments'))
        ->assertSeeText('Khai Tâm 1')
        ->assertSeeText('Ấu 1');
});

test('sector assignment overview hides edit actions without update permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.sector-assignment.view');

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Xưng Tội 1',
        'sector_name' => 'Thiếu 1',
    ]);

    $this->actingAs($user);

    Livewire::test(SectorAssignmentList::class, ['academicYearId' => $academicYear->id])
        ->assertDontSeeText(__('Assign leaders'));
});

test('sector leader assignments can be managed from the sector assignment module', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.sector-assignment.view',
        'arrangement.sector-assignment.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Bao Đồng 1',
        'sector_name' => 'Thiếu 1',
    ]);

    $sectorHead = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Trưởng Thiếu',
    ]);
    $sectorHead->assignRole(sectorAssignmentPersonnelRole('Trưởng Ngành Thiếu'));

    $viceSectorHead = User::factory()->create([
        'last_name' => 'Phạm',
        'name' => 'Phó Thiếu',
    ]);
    $viceSectorHead->assignRole(sectorAssignmentPersonnelRole('Phó Ngành Thiếu'));

    $leader = User::factory()->create([
        'last_name' => 'Hoàng',
        'name' => 'HT 1',
    ]);
    $leader->assignRole(sectorAssignmentPersonnelRole('Huynh Trưởng'));

    $this->actingAs($user);

    Livewire::test(SectorAssignmentActions::class, ['academicYearId' => $academicYear->id])
        ->call('openLeaderAssignmentModal', $course->sector_name)
        ->set('sectorHeadUserId', $sectorHead->id)
        ->set('viceSectorHeadUserId', $viceSectorHead->id)
        ->set('leaderIds', [$leader->id])
        ->call('saveLeaderAssignments')
        ->assertHasNoErrors();

    expect(AcademicYearSectorStaff::query()
        ->where('academic_year_id', $academicYear->id)
        ->where('sector_name', $course->sector_name)
        ->orderBy('assignment_type')
        ->get()
        ->map(fn (AcademicYearSectorStaff $assignment): array => [
            'user_id' => $assignment->user_id,
            'assignment_type' => $assignment->assignment_type,
        ])
        ->all())->toBe([
            [
                'user_id' => $viceSectorHead->id,
                'assignment_type' => 'assistant_sector_leader',
            ],
            [
                'user_id' => $leader->id,
                'assignment_type' => 'leader',
            ],
            [
                'user_id' => $sectorHead->id,
                'assignment_type' => 'sector_leader',
            ],
        ]);

    $activity = Activity::query()
        ->where('log_name', 'sector_assignments')
        ->where('event', 'updated')
        ->where('causer_id', $user->id)
        ->where('subject_type', AcademicYear::class)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->description)->toBe(__('Updated sector leader assignments'))
        ->and($activity?->properties['attributes']['academic_year'])->toBe($academicYear->name)
        ->and($activity?->properties['attributes']['sector'])->toBe($course->sector_name)
        ->and($activity?->properties['attributes']['sector_head_after'])->toBe($sectorHead->full_name)
        ->and($activity?->properties['attributes']['vice_sector_head_after'])->toBe($viceSectorHead->full_name)
        ->and($activity?->properties['attributes']['leaders_after'])->toBe([$leader->full_name]);
});
