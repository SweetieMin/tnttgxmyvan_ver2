<?php

use App\Livewire\Admin\Management\AcademicYear\AcademicYearActions;
use App\Livewire\Admin\Management\AcademicYear\AcademicYearList;
use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Program;
use App\Models\User;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'management.academic-year.view',
        'management.academic-year.create',
        'management.academic-year.update',
        'management.academic-year.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the academic years page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.academic-year.view');

    $response = $this->actingAs($user)->get(route('admin.management.academic-years'));

    $response->assertOk()
        ->assertSeeText(__('Academic years'));
});

test('academic year list supports filament search and status filters', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.view',
        'management.academic-year.update',
        'management.academic-year.delete',
    ]);

    $ongoingAcademicYear = AcademicYear::factory()->create([
        'name' => 'NK24-25',
        'status_academic' => 'ongoing',
    ]);

    $upcomingAcademicYear = AcademicYear::factory()->create([
        'name' => 'NK26-27',
        'status_academic' => 'upcoming',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicYearList::class)
        ->assertSee($ongoingAcademicYear->name)
        ->assertSee($upcomingAcademicYear->name)
        ->set('tableSearch', 'NK24')
        ->assertSee($ongoingAcademicYear->name)
        ->assertDontSee($upcomingAcademicYear->name);

    Livewire::test(AcademicYearList::class)
        ->set('tableFilters', [
            'status_academic' => [
                'value' => 'ongoing',
            ],
        ])
        ->assertSee($ongoingAcademicYear->name)
        ->assertDontSee($upcomingAcademicYear->name);
});

test('academic years can be created updated and deleted from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.view',
        'management.academic-year.create',
        'management.academic-year.update',
        'management.academic-year.delete',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->set('start_year', 2026)
        ->set('end_year', 2027)
        ->call('continueToAcademicYearDetails')
        ->set('catechism_start_date', '2026-09-01')
        ->set('catechism_end_date', '2027-05-31')
        ->set('catechism_avg_score', '6.50')
        ->set('catechism_training_score', '7.00')
        ->set('activity_start_date', '2026-09-05')
        ->set('activity_end_date', '2027-05-28')
        ->set('activity_score', 180)
        ->set('status_academic', 'upcoming')
        ->call('saveAcademicYear')
        ->assertHasNoErrors();

    $academicYear = AcademicYear::query()->where('name', 'NK26-27')->firstOrFail();

    Livewire::test(AcademicYearActions::class)
        ->call('openEditModal', $academicYear->id)
        ->set('start_year', 2027)
        ->set('end_year', 2028)
        ->set('status_academic', 'ongoing')
        ->call('saveAcademicYear')
        ->assertHasNoErrors();

    expect(AcademicYear::query()->where('name', 'NK27-28')->exists())->toBeTrue();

    Livewire::test(AcademicYearActions::class)
        ->call('confirmDeleteAcademicYear', $academicYear->fresh()->id)
        ->call('deleteAcademicYear')
        ->assertHasNoErrors();

    expect(AcademicYear::query()->where('name', 'NK27-28')->exists())->toBeFalse();
});

test('creating an academic year can automatically create catechism sector classes from programs', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.view',
        'management.academic-year.create',
    ]);

    $firstProgram = Program::factory()->create([
        'ordering' => 1,
        'course' => 'Khai Tam 1',
        'sector' => 'Au 1',
    ]);

    $secondProgram = Program::factory()->create([
        'ordering' => 2,
        'course' => 'Khai Tam 2',
        'sector' => 'Au 2',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->set('start_year', 2028)
        ->set('end_year', 2029)
        ->call('continueToAcademicYearDetails')
        ->set('catechism_start_date', '2028-09-01')
        ->set('catechism_end_date', '2029-05-31')
        ->set('catechism_avg_score', '6.50')
        ->set('catechism_training_score', '7.00')
        ->set('activity_start_date', '2028-09-05')
        ->set('activity_end_date', '2029-05-28')
        ->set('activity_score', 180)
        ->set('status_academic', 'upcoming')
        ->set('should_create_academic_courses', true)
        ->call('saveAcademicYear')
        ->assertHasNoErrors();

    $academicYear = AcademicYear::query()->where('name', 'NK28-29')->firstOrFail();

    $academicCourses = AcademicCourse::query()
        ->where('academic_year_id', $academicYear->id)
        ->orderBy('ordering')
        ->get();

    expect($academicCourses)->toHaveCount(2)
        ->and($academicCourses[0]->program_id)->toBe($firstProgram->id)
        ->and($academicCourses[0]->course_name)->toBe('Khai Tam 1')
        ->and($academicCourses[0]->sector_name)->toBe('Au 1')
        ->and($academicCourses[0]->catechism_avg_score)->toBe('6.50')
        ->and($academicCourses[0]->catechism_training_score)->toBe('7.00')
        ->and($academicCourses[0]->activity_score)->toBe(180)
        ->and($academicCourses[1]->program_id)->toBe($secondProgram->id)
        ->and($academicCourses[1]->course_name)->toBe('Khai Tam 2')
        ->and($academicCourses[1]->sector_name)->toBe('Au 2');
});

test('creating an academic year without opting in does not create catechism sector classes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.view',
        'management.academic-year.create',
    ]);

    Program::factory()->create([
        'ordering' => 1,
        'course' => 'Xung Toi 1',
        'sector' => 'Au 1',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->set('start_year', 2030)
        ->set('end_year', 2031)
        ->call('continueToAcademicYearDetails')
        ->set('catechism_start_date', '2030-09-01')
        ->set('catechism_end_date', '2031-05-31')
        ->set('activity_start_date', '2030-09-05')
        ->set('activity_end_date', '2031-05-28')
        ->set('status_academic', 'upcoming')
        ->assertSet('should_create_academic_courses', false)
        ->call('saveAcademicYear')
        ->assertHasNoErrors();

    $academicYear = AcademicYear::query()->where('name', 'NK30-31')->firstOrFail();

    expect(AcademicCourse::query()->where('academic_year_id', $academicYear->id)->count())->toBe(0);
});

test('editing an academic year asks for confirmation before syncing existing catechism sector classes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.view',
        'management.academic-year.update',
    ]);

    $academicYear = AcademicYear::factory()->create([
        'catechism_avg_score' => 5.00,
        'catechism_training_score' => 5.00,
        'activity_score' => 150,
    ]);

    $firstProgram = Program::factory()->create([
        'ordering' => 1,
        'course' => 'Them Suc 1',
        'sector' => 'Thieu 1',
    ]);

    $secondProgram = Program::factory()->create([
        'ordering' => 2,
        'course' => 'Them Suc 2',
        'sector' => 'Thieu 2',
    ]);

    $existingAcademicCourse = AcademicCourse::factory()->create([
        'academic_year_id' => $academicYear->id,
        'program_id' => $firstProgram->id,
        'ordering' => 9,
        'course_name' => 'Custom Them Suc 1',
        'sector_name' => 'Custom Thieu 1',
        'catechism_avg_score' => 4.00,
        'catechism_training_score' => 4.50,
        'activity_score' => 120,
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->call('openEditModal', $academicYear->id)
        ->set('catechism_avg_score', '6.50')
        ->set('catechism_training_score', '7.00')
        ->set('activity_score', 180)
        ->set('should_create_academic_courses', true)
        ->call('saveAcademicYear')
        ->assertSet('showSyncAcademicCoursesConfirmModal', true)
        ->call('confirmSyncAcademicCoursesAndSave')
        ->assertHasNoErrors();

    $syncedAcademicCourses = AcademicCourse::query()
        ->where('academic_year_id', $academicYear->id)
        ->orderBy('ordering')
        ->get();

    expect($syncedAcademicCourses)->toHaveCount(2)
        ->and($syncedAcademicCourses[0]->id)->toBe($existingAcademicCourse->id)
        ->and($syncedAcademicCourses[0]->course_name)->toBe('Them Suc 1')
        ->and($syncedAcademicCourses[0]->sector_name)->toBe('Thieu 1')
        ->and($syncedAcademicCourses[0]->catechism_avg_score)->toBe('6.50')
        ->and($syncedAcademicCourses[0]->catechism_training_score)->toBe('7.00')
        ->and($syncedAcademicCourses[0]->activity_score)->toBe(180)
        ->and($syncedAcademicCourses[1]->program_id)->toBe($secondProgram->id)
        ->and($syncedAcademicCourses[1]->course_name)->toBe('Them Suc 2')
        ->and($syncedAcademicCourses[1]->sector_name)->toBe('Thieu 2');
});

test('academic year save button only appears after the form changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.academic-year.create',
        'management.academic-year.update',
    ]);

    $academicYear = AcademicYear::factory()->create();

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->call('openCreateModal')
        ->assertSet('showAcademicYearDetails', false)
        ->call('shouldShowSaveAcademicYearButton')
        ->assertReturned(false)
        ->set('start_year', 2030)
        ->set('end_year', 2031)
        ->call('continueToAcademicYearDetails')
        ->assertSet('showAcademicYearDetails', true)
        ->call('shouldShowSaveAcademicYearButton')
        ->assertReturned(true);

    Livewire::test(AcademicYearActions::class)
        ->call('openEditModal', $academicYear->id)
        ->assertSet('showAcademicYearDetails', true)
        ->call('shouldShowSaveAcademicYearButton')
        ->assertReturned(false)
        ->set('end_year', ((int) $academicYear->catechism_end_date?->year) + 1)
        ->call('shouldShowSaveAcademicYearButton')
        ->assertReturned(true);
});

test('academic year defaults schedule dates from the selected years', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.academic-year.create');

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->call('openCreateModal')
        ->assertSet('start_year', now()->year)
        ->assertSet('end_year', now()->year + 1)
        ->assertSet('catechism_start_date', sprintf('%s-09-01', now()->year))
        ->assertSet('catechism_end_date', sprintf('%s-07-31', now()->year + 1))
        ->assertSet('activity_start_date', sprintf('%s-09-01', now()->year))
        ->assertSet('activity_end_date', sprintf('%s-07-31', now()->year + 1))
        ->set('start_year', 2028)
        ->set('end_year', 2029)
        ->assertSet('catechism_start_date', '2028-09-01')
        ->assertSet('catechism_end_date', '2029-07-31')
        ->assertSet('activity_start_date', '2028-09-01')
        ->assertSet('activity_end_date', '2029-07-31');
});

test('create academic year keeps the year selection step when the code already exists', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.academic-year.create');

    AcademicYear::factory()->create([
        'name' => 'NK26-27',
    ]);

    $this->actingAs($user);

    Livewire::test(AcademicYearActions::class)
        ->call('openCreateModal')
        ->set('start_year', 2026)
        ->set('end_year', 2027)
        ->call('continueToAcademicYearDetails')
        ->assertSet('showAcademicYearDetails', false)
        ->assertHasErrors(['start_year']);
});

test('academic years are ordered by current status priority', function () {
    AcademicYear::factory()->create([
        'name' => 'NK24-25',
        'status_academic' => 'finished',
        'catechism_start_date' => '2024-09-01',
    ]);

    AcademicYear::factory()->create([
        'name' => 'NK26-27',
        'status_academic' => 'upcoming',
        'catechism_start_date' => '2026-09-01',
    ]);

    AcademicYear::factory()->create([
        'name' => 'NK25-26',
        'status_academic' => 'ongoing',
        'catechism_start_date' => '2025-09-01',
    ]);

    $academicYears = app(AcademicYearRepositoryInterface::class)
        ->paginateForAdmin('', 15)
        ->getCollection()
        ->pluck('name')
        ->values()
        ->all();

    expect($academicYears)->toBe([
        'NK25-26',
        'NK26-27',
        'NK24-25',
    ]);
});
