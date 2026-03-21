<?php

use App\Livewire\Admin\Management\AcademicYear\AcademicYearActions;
use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\User;
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
