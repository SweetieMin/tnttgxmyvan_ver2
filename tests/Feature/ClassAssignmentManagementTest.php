<?php

use App\Livewire\Admin\Arrangement\Enrollments\EnrollmentIndex;
use App\Livewire\Admin\Arrangement\Enrollments\EnrollmentList;
use App\Models\AcademicCourse;
use App\Models\AcademicEnrollment;
use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use App\Models\UserReligiousProfile;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'arrangement.enrollment.view',
        'arrangement.enrollment.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

function enrollmentChildRole(): Role
{
    return Role::findOrCreate('Thiếu Nhi', 'web');
}

test('authorized users can visit the enrollment workspace', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.enrollment.view');

    $academicYear = AcademicYear::factory()->create([
        'name' => 'NK26-27',
        'status_academic' => 'ongoing',
    ]);

    $this->actingAs($user)
        ->get(route('admin.arrangement.enrollments'))
        ->assertOk()
        ->assertSeeText(__('Enrollments'))
        ->assertSeeText(__('Enrollment workspace'))
        ->assertSeeText($academicYear->name);
});

test('enrollment index defaults to the ongoing academic year', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('arrangement.enrollment.view');

    $ongoingAcademicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    AcademicYear::factory()->create([
        'status_academic' => 'finished',
    ]);

    $this->actingAs($user);

    Livewire::test(EnrollmentIndex::class)
        ->assertSet('academicYearId', $ongoingAcademicYear->id);
});

test('enrollment workspace can prepare promotion suggestions and save assignments', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.enrollment.view',
        'arrangement.enrollment.update',
    ]);

    $previousAcademicYear = AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'finished',
        'catechism_start_date' => '2025-09-01',
    ]);

    $currentAcademicYear = AcademicYear::factory()->create([
        'name' => 'NK26-27',
        'status_academic' => 'ongoing',
        'catechism_start_date' => '2026-09-01',
    ]);

    $firstProgram = Program::factory()->create(['ordering' => 1]);
    $secondProgram = Program::factory()->create(['ordering' => 2]);

    $previousFirstCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $previousAcademicYear->id,
        'program_id' => $firstProgram->id,
        'ordering' => 1,
        'course_name' => 'Khai Tâm 1',
        'sector_name' => 'Tiền Ấu 1',
    ]);

    $currentFirstCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $currentAcademicYear->id,
        'program_id' => $firstProgram->id,
        'ordering' => 1,
        'course_name' => 'Khai Tâm 1',
        'sector_name' => 'Tiền Ấu 1',
    ]);

    $currentSecondCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $currentAcademicYear->id,
        'program_id' => $secondProgram->id,
        'ordering' => 2,
        'course_name' => 'Khai Tâm 2',
        'sector_name' => 'Tiền Ấu 2',
    ]);

    $promotedChild = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'An',
    ]);
    $promotedChild->assignRole(enrollmentChildRole());

    $repeatingChild = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Bình',
    ]);
    $repeatingChild->assignRole(enrollmentChildRole());

    AcademicEnrollment::factory()->create([
        'user_id' => $promotedChild->id,
        'academic_year_id' => $previousAcademicYear->id,
        'academic_course_id' => $previousFirstCourse->id,
        'status' => 'passed',
        'is_eligible_for_promotion' => true,
    ]);

    AcademicEnrollment::factory()->create([
        'user_id' => $repeatingChild->id,
        'academic_year_id' => $previousAcademicYear->id,
        'academic_course_id' => $previousFirstCourse->id,
        'status' => 'pending_review',
        'is_eligible_for_promotion' => false,
    ]);

    $this->actingAs($user);

    Livewire::test(EnrollmentList::class, ['academicYearId' => $currentAcademicYear->id])
        ->assertSeeText('Nguyễn An')
        ->assertDontSeeText('Trần Bình')
        ->set('previousResultFilter', 'pending_review')
        ->assertSeeText('Trần Bình')
        ->assertDontSeeText('Nguyễn An')
        ->set('previousResultFilter', 'passed')
        ->call('applyPromotionSuggestions')
        ->assertSet('courseSelections.'.$promotedChild->id, $currentSecondCourse->id)
        ->call('saveAssignments')
        ->assertHasNoErrors();

    expect(AcademicEnrollment::query()
        ->where('academic_year_id', $currentAcademicYear->id)
        ->where('user_id', $promotedChild->id)
        ->value('academic_course_id'))->toBe($currentSecondCourse->id)
        ->and(AcademicEnrollment::query()
            ->where('academic_year_id', $currentAcademicYear->id)
            ->where('user_id', $repeatingChild->id)
            ->exists())->toBeFalse();
});

test('enrollment workspace can apply a bulk class selection to selected children', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.enrollment.view',
        'arrangement.enrollment.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $program = Program::factory()->create(['ordering' => 1]);
    $course = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $program->id,
        'ordering' => 1,
        'course_name' => 'Xưng Tội 1',
        'sector_name' => 'Ấu 1',
    ]);

    $firstChild = User::factory()->create();
    $firstChild->assignRole(enrollmentChildRole());

    $secondChild = User::factory()->create();
    $secondChild->assignRole(enrollmentChildRole());

    $this->actingAs($user);

    Livewire::test(EnrollmentList::class, ['academicYearId' => $academicYear->id])
        ->set('selectedUserIds', [$firstChild->id, $secondChild->id])
        ->set('bulkAcademicCourseId', $course->id)
        ->call('applyBulkCourseSelection')
        ->assertSet('courseSelections.'.$firstChild->id, $course->id)
        ->assertSet('courseSelections.'.$secondChild->id, $course->id);
});

test('enrollment workspace can filter by class and orders rows by class', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.enrollment.view',
        'arrangement.enrollment.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'status_academic' => 'ongoing',
    ]);

    $firstProgram = Program::factory()->create(['ordering' => 1]);
    $secondProgram = Program::factory()->create(['ordering' => 2]);

    $firstCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $firstProgram->id,
        'ordering' => 1,
        'course_name' => 'Khai Tâm 1',
        'sector_name' => 'Tiền Ấu 1',
    ]);

    $secondCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $secondProgram->id,
        'ordering' => 2,
        'course_name' => 'Khai Tâm 2',
        'sector_name' => 'Tiền Ấu 2',
    ]);

    $firstChild = User::factory()->create([
        'last_name' => 'Nguyễn',
        'name' => 'An',
    ]);
    $firstChild->assignRole(enrollmentChildRole());

    $secondChild = User::factory()->create([
        'last_name' => 'Trần',
        'name' => 'Bình',
    ]);
    $secondChild->assignRole(enrollmentChildRole());

    AcademicEnrollment::factory()->create([
        'user_id' => $secondChild->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $secondCourse->id,
        'status' => 'studying',
    ]);

    AcademicEnrollment::factory()->create([
        'user_id' => $firstChild->id,
        'academic_year_id' => $academicYear->id,
        'academic_course_id' => $firstCourse->id,
        'status' => 'studying',
    ]);

    $this->actingAs($user);

    Livewire::test(EnrollmentList::class, ['academicYearId' => $academicYear->id])
        ->assertSeeText('Khai Tâm 1')
        ->assertSeeText('Khai Tâm 2')
        ->assertSeeTextInOrder([
            'Nguyễn An',
            'Trần Bình',
        ])
        ->set('classFilterAcademicCourseId', $secondCourse->id)
        ->assertSeeText('Trần Bình')
        ->assertDontSeeText('Nguyễn An');
});

test('enrollment workspace marks a child as graduated after passing the final class', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'arrangement.enrollment.view',
        'arrangement.enrollment.update',
    ]);

    $previousAcademicYear = AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'finished',
        'catechism_start_date' => '2025-09-01',
    ]);

    $currentAcademicYear = AcademicYear::factory()->create([
        'name' => 'NK26-27',
        'status_academic' => 'ongoing',
        'catechism_start_date' => '2026-09-01',
    ]);

    $finalProgram = Program::factory()->create(['ordering' => 12]);

    $previousFinalCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $previousAcademicYear->id,
        'program_id' => $finalProgram->id,
        'ordering' => 12,
        'course_name' => 'Vào Đời',
        'sector_name' => 'Nghĩa 3',
    ]);

    AcademicCourse::factory()->create([
        'academic_year_id' => $currentAcademicYear->id,
        'program_id' => $finalProgram->id,
        'ordering' => 12,
        'course_name' => 'Vào Đời',
        'sector_name' => 'Nghĩa 3',
    ]);

    $graduatingChild = User::factory()->create([
        'last_name' => 'Lê',
        'name' => 'Minh',
    ]);
    $graduatingChild->assignRole(enrollmentChildRole());

    AcademicEnrollment::factory()->create([
        'user_id' => $graduatingChild->id,
        'academic_year_id' => $previousAcademicYear->id,
        'academic_course_id' => $previousFinalCourse->id,
        'status' => 'passed',
        'is_eligible_for_promotion' => true,
    ]);

    $this->actingAs($user);

    Livewire::test(EnrollmentList::class, ['academicYearId' => $currentAcademicYear->id])
        ->call('applyPromotionSuggestions')
        ->assertSet('courseSelections.'.$graduatingChild->id, '')
        ->set('selectedUserIds', [$graduatingChild->id])
        ->set('bulkAcademicCourseId', $currentAcademicYear->academicCourses()->value('id'))
        ->call('applyBulkCourseSelection')
        ->assertSet('courseSelections.'.$graduatingChild->id, '')
        ->call('saveAssignments')
        ->assertHasNoErrors();

    expect(AcademicEnrollment::query()
        ->where('academic_year_id', $currentAcademicYear->id)
        ->where('user_id', $graduatingChild->id)
        ->exists())->toBeFalse()
        ->and(UserReligiousProfile::query()
            ->where('user_id', $graduatingChild->id)
            ->value('status_religious'))->toBe('graduated');
});
