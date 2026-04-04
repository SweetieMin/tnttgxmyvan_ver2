<?php

use App\Livewire\Admin\Management\AcademicCourses\AcademicCourseActions;
use App\Livewire\Admin\Management\AcademicCourses\AcademicCourseIndex;
use App\Livewire\Admin\Management\AcademicCourses\AcademicCourseList;
use App\Models\AcademicCourse;
use App\Models\AcademicCourseStaff;
use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\PersonnelRoleGroup;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'management.academic-course.view',
        'management.academic-course.create',
        'management.academic-course.update',
        'management.academic-course.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

function academicCoursePersonnelRole(string $roleName): Role
{
    $role = Role::findOrCreate($roleName, 'web');
    $groupKeys = match ($roleName) {
        'Trưởng Giáo Lý', 'Phó Giáo Lý', 'Giáo Lý Viên' => ['catechists'],
        'Xứ Đoàn Trưởng', 'Xứ Đoàn Phó', 'Huynh Trưởng', 'Dự Trưởng' => ['leaders'],
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

test('authorized users can visit the academic courses page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.academic-course.view');

    $response = $this->actingAs($user)->get(route('admin.management.academic-courses'));

    $response->assertOk()
        ->assertSeeText(__('Catechism - sector classes'));
});

test('academic courses can be created updated and deleted from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.create',
        'management.academic-course.update',
        'management.academic-course.delete',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'catechism_avg_score' => 6.50,
        'catechism_training_score' => 7.00,
        'activity_score' => 180,
    ]);

    $program = Program::factory()->create([
        'ordering' => 4,
        'course' => 'Them Suc 2A',
        'sector' => 'Thieu 2A',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicCourseActions::class)
        ->call('openCreateModal')
        ->set('academic_year_id', $academicYear->id)
        ->set('program_id', $program->id)
        ->assertSet('ordering', 4)
        ->assertSet('course_name', 'Them Suc 2A')
        ->assertSet('sector_name', 'Thieu 2A')
        ->assertSet('catechism_avg_score', '6.50')
        ->assertSet('catechism_training_score', '7.00')
        ->assertSet('activity_score', 180)
        ->set('course_name', 'Them Suc 2B')
        ->set('sector_name', 'Thieu 2B')
        ->call('saveAcademicCourse')
        ->assertHasNoErrors();

    $academicCourse = AcademicCourse::query()->where('course_name', 'Them Suc 2B')->firstOrFail();

    Livewire::test(AcademicCourseActions::class)
        ->call('openEditModal', $academicCourse->id)
        ->set('sector_name', 'Thieu 2B Nang Cao')
        ->set('activity_score', 200)
        ->call('saveAcademicCourse')
        ->assertHasNoErrors();

    expect($academicCourse->fresh()->sector_name)->toBe('Thieu 2B Nang Cao')
        ->and($academicCourse->fresh()->activity_score)->toBe(200);

    Livewire::test(AcademicCourseActions::class)
        ->call('confirmDeleteAcademicCourse', $academicCourse->id)
        ->call('deleteAcademicCourse')
        ->assertHasNoErrors();

    expect(AcademicCourse::query()->whereKey($academicCourse->id)->exists())->toBeFalse();
});

test('academic course index defaults to the ongoing academic year', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.academic-course.view');
    $user->givePermissionTo('management.academic-course.create');

    $ongoingAcademicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    AcademicYear::factory()->create([
        'status_academic' => 'finished',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicCourseIndex::class)
        ->call('openCreateModal')
        ->assertDispatched('open-create-academic-course-modal', academicYearId: $ongoingAcademicYear->id);
});

test('academic course list filters and reorders within the selected academic year', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.update',
    ]);

    $ongoingAcademicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $finishedAcademicYear = AcademicYear::factory()->create([
        'status_academic' => 'finished',
    ]);

    $program = Program::factory()->create();

    $firstCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $ongoingAcademicYear->id,
        'program_id' => $program->id,
        'ordering' => 1,
        'course_name' => 'Them Suc 1A',
        'sector_name' => 'Thieu 1A',
    ]);

    $secondCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $ongoingAcademicYear->id,
        'program_id' => $program->id,
        'ordering' => 2,
        'course_name' => 'Them Suc 1B',
        'sector_name' => 'Thieu 1B',
    ]);

    $otherAcademicYearCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $finishedAcademicYear->id,
        'program_id' => $program->id,
        'ordering' => 1,
        'course_name' => 'Them Suc 2A',
        'sector_name' => 'Thieu 2A',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(AcademicCourseList::class)
        ->assertCanSeeTableRecords([
            $firstCourse,
            $secondCourse,
        ], inOrder: true)
        ->assertCanNotSeeTableRecords([$otherAcademicYearCourse])
        ->call('reorderTable', [
            $secondCourse->id,
            $firstCourse->id,
        ])
        ->assertHasNoErrors();

    expect($component->instance()->getTable()->isReorderable())->toBeTrue();

    expect($secondCourse->fresh()->ordering)->toBe(1)
        ->and($firstCourse->fresh()->ordering)->toBe(2)
        ->and($otherAcademicYearCourse->fresh()->ordering)->toBe(1);
});

test('academic course list shows all academic years when the filter is empty', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.academic-course.view');

    $firstAcademicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $secondAcademicYear = AcademicYear::factory()->create([
        'status_academic' => 'finished',
    ]);

    $program = Program::factory()->create();

    $firstCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $firstAcademicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Khai Tam 1A',
        'sector_name' => 'Au 1A',
    ]);

    $secondCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $secondAcademicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Khai Tam 2A',
        'sector_name' => 'Au 2A',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicCourseList::class)
        ->removeTableFilter('academic_year_id', 'value')
        ->assertCanSeeTableRecords([
            $firstCourse,
            $secondCourse,
        ]);
});

test('academic course filament table supports searching filters and record actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.create',
        'management.academic-course.update',
        'management.academic-course.delete',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    $activeCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Khai Tam 1A',
        'sector_name' => 'Au 1A',
        'is_active' => true,
    ]);

    $inactiveCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'course_name' => 'Khai Tam 1B',
        'sector_name' => 'Au 1B',
        'is_active' => false,
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicCourseList::class)
        ->assertTableFilterExists('academic_year_id')
        ->assertTableColumnExists('required_scores')
        ->assertCanSeeTableRecords([
            $activeCourse,
            $inactiveCourse,
        ])
        ->assertSeeText('Khai Tam 1A')
        ->assertSeeText('Au 1A')
        ->assertSeeText(__('Active'))
        ->assertSeeText(__('Catechism average'))
        ->assertSeeText('5.00')
        ->assertTableActionExists('edit', record: $activeCourse)
        ->assertTableActionExists('duplicate', record: $activeCourse)
        ->assertTableActionExists('assignCatechists', record: $activeCourse)
        ->assertTableActionExists('assignLeaders', record: $activeCourse)
        ->assertTableActionExists('delete', record: $activeCourse)
        ->searchTable('Au 1B')
        ->assertCanSeeTableRecords([$inactiveCourse])
        ->assertCanNotSeeTableRecords([$activeCourse])
        ->searchTable('')
        ->filterTable('academic_year_id', $academicYear->id)
        ->filterTable('is_active', '1')
        ->assertCanSeeTableRecords([$activeCourse])
        ->assertCanNotSeeTableRecords([$inactiveCourse]);
});

test('academic course catechist assignments can be managed from the table action', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
    ]);

    $primaryCatechist = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'Chính',
    ]);
    $primaryCatechist->assignRole(academicCoursePersonnelRole('Giáo Lý Viên'));

    $assistantCatechist = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Phụ',
    ]);
    $assistantCatechist->assignRole(academicCoursePersonnelRole('Phó Giáo Lý'));

    $this->actingAs($user);

    Livewire::test(AcademicCourseList::class)
        ->callTableAction('assignCatechists', $course, data: [
            'primaryCatechistId' => (string) $primaryCatechist->id,
            'assistantCatechistIds' => [(string) $assistantCatechist->id],
        ])
        ->assertHasNoTableActionErrors();

    expect(AcademicCourseStaff::query()
        ->where('academic_course_id', $course->id)
        ->orderBy('assignment_type')
        ->get()
        ->map(fn (AcademicCourseStaff $assignment): array => [
            'user_id' => $assignment->user_id,
            'assignment_type' => $assignment->assignment_type,
            'is_primary' => $assignment->is_primary,
        ])
        ->all())->toBe([
            [
                'user_id' => $assistantCatechist->id,
                'assignment_type' => 'assistant_catechist',
                'is_primary' => false,
            ],
            [
                'user_id' => $primaryCatechist->id,
                'assignment_type' => 'catechist',
                'is_primary' => true,
            ],
        ]);
});

test('academic course leader assignments can be managed from the table action', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
    ]);

    $primaryLeader = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Trưởng',
    ]);
    $primaryLeader->assignRole(academicCoursePersonnelRole('Huynh Trưởng'));

    $assistantLeader = User::factory()->create([
        'last_name' => 'Phạm',
        'name' => 'Phụ Tá',
    ]);
    $assistantLeader->assignRole(academicCoursePersonnelRole('Dự Trưởng'));

    $this->actingAs($user);

    Livewire::test(AcademicCourseList::class)
        ->callTableAction('assignLeaders', $course, data: [
            'primaryLeaderId' => (string) $primaryLeader->id,
            'assistantLeaderIds' => [(string) $assistantLeader->id],
        ])
        ->assertHasNoTableActionErrors();

    expect(AcademicCourseStaff::query()
        ->where('academic_course_id', $course->id)
        ->orderBy('assignment_type')
        ->get()
        ->map(fn (AcademicCourseStaff $assignment): array => [
            'user_id' => $assignment->user_id,
            'assignment_type' => $assignment->assignment_type,
            'is_primary' => $assignment->is_primary,
        ])
        ->all())->toBe([
            [
                'user_id' => $assistantLeader->id,
                'assignment_type' => 'assistant_leader',
                'is_primary' => false,
            ],
            [
                'user_id' => $primaryLeader->id,
                'assignment_type' => 'leader',
                'is_primary' => true,
            ],
        ]);
});

test('academic course catechist assignment defaults to head and vice catechists as assistants when empty', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
    ]);

    $headCatechist = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'Trưởng GL',
    ]);
    $headCatechist->assignRole(academicCoursePersonnelRole('Trưởng Giáo Lý'));

    $viceCatechist = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Phó GL',
    ]);
    $viceCatechist->assignRole(academicCoursePersonnelRole('Phó Giáo Lý'));

    $this->actingAs($user);

    Livewire::test(AcademicCourseList::class)
        ->mountTableAction('assignCatechists', $course)
        ->assertTableActionDataSet([
            'primaryCatechistId' => null,
            'assistantCatechistIds' => [$headCatechist->id, $viceCatechist->id],
        ]);
});

test('academic course leader assignment defaults to parish leader and vice leader as assistants when empty', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create();

    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
    ]);

    $headLeader = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Xứ Đoàn Trưởng',
    ]);
    $headLeader->assignRole(academicCoursePersonnelRole('Xứ Đoàn Trưởng'));

    $viceLeader = User::factory()->create([
        'last_name' => 'Phạm',
        'name' => 'Xứ Đoàn Phó',
    ]);
    $viceLeader->assignRole(academicCoursePersonnelRole('Xứ Đoàn Phó'));

    $this->actingAs($user);

    Livewire::test(AcademicCourseList::class)
        ->mountTableAction('assignLeaders', $course)
        ->assertTableActionDataSet([
            'primaryLeaderId' => null,
            'assistantLeaderIds' => [$headLeader->id, $viceLeader->id],
        ]);
});

test('academic courses can be duplicated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-course.view',
        'management.academic-course.create',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
        'catechism_avg_score' => 6.50,
        'catechism_training_score' => 7.00,
        'activity_score' => 180,
    ]);

    $program = Program::factory()->create([
        'ordering' => 4,
        'course' => 'Them Suc 2A',
        'sector' => 'Thieu 2A',
    ]);

    $academicCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'ordering' => 4,
        'course_name' => 'Them Suc 2A',
        'sector_name' => 'Thieu 2A',
        'catechism_avg_score' => 6.50,
        'catechism_training_score' => 7.00,
        'activity_score' => 180,
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicCourseActions::class)
        ->call('openDuplicateModal', $academicCourse->id)
        ->assertSet('editingAcademicCourseId', null)
        ->assertSet('academic_year_id', $academicYear->id)
        ->assertSet('program_id', $program->id)
        ->assertSet('ordering', 5)
        ->assertSet('course_name', 'Them Suc 2A ('.__('Copy').')')
        ->assertSet('sector_name', 'Thieu 2A ('.__('Copy').')')
        ->assertSet('isDuplicatingAcademicCourse', true)
        ->call('saveAcademicCourse')
        ->assertHasNoErrors();

    $duplicatedAcademicCourse = AcademicCourse::query()
        ->where('course_name', 'Them Suc 2A ('.__('Copy').')')
        ->first();

    expect($duplicatedAcademicCourse)->not->toBeNull()
        ->and($duplicatedAcademicCourse?->sector_name)->toBe('Thieu 2A ('.__('Copy').')')
        ->and($duplicatedAcademicCourse?->ordering)->toBe(5);
});
