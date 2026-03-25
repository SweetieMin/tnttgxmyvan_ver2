<?php

use App\Livewire\Admin\Management\Regulations\RegulationActions;
use App\Livewire\Admin\Management\Regulations\RegulationList;
use App\Models\Permission;
use App\Models\Regulation;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'management.regulation.view',
        'management.regulation.create',
        'management.regulation.update',
        'management.regulation.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the regulations page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.regulation.view');

    $response = $this->actingAs($user)->get(route('admin.management.regulations'));

    $response->assertOk()
        ->assertSeeText(__('Regulations'));
});

test('regulations can be created updated and deleted from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.regulation.view',
        'management.regulation.create',
        'management.regulation.update',
        'management.regulation.delete',
    ]);

    $this->actingAs($user);

    Livewire::test(RegulationActions::class)
        ->call('openCreateModal')
        ->set('description', 'Đi học đúng giờ')
        ->set('type', 'plus')
        ->set('status', 'applied')
        ->set('point_value', 10)
        ->call('saveRegulation')
        ->assertHasNoErrors();

    $regulation = Regulation::query()->where('description', 'Đi học đúng giờ')->firstOrFail();

    Livewire::test(RegulationActions::class)
        ->call('openEditModal', $regulation->id)
        ->set('description', 'Đi học đúng giờ và đầy đủ')
        ->call('saveRegulation')
        ->assertHasNoErrors();

    expect($regulation->fresh()->description)->toBe('Đi học đúng giờ và đầy đủ');

    Livewire::test(RegulationActions::class)
        ->call('confirmDeleteRegulation', $regulation->id)
        ->call('deleteRegulation')
        ->assertHasNoErrors();

    expect(Regulation::query()->whereKey($regulation->id)->exists())->toBeFalse();
});

test('regulation save button only appears after the form changes on edit', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.regulation.create',
        'management.regulation.update',
    ]);

    $regulation = Regulation::factory()->create();

    $this->actingAs($user);

    Livewire::test(RegulationActions::class)
        ->call('openCreateModal')
        ->call('shouldShowSaveRegulationButton')
        ->assertReturned(true);

    Livewire::test(RegulationActions::class)
        ->call('openEditModal', $regulation->id)
        ->call('shouldShowSaveRegulationButton')
        ->assertReturned(false)
        ->set('point_value', $regulation->points + 5)
        ->call('shouldShowSaveRegulationButton')
        ->assertReturned(true);
});

test('regulation points show a validation error when the value is not an integer', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.regulation.create',
    ]);

    $this->actingAs($user);

    Livewire::test(RegulationActions::class)
        ->call('openCreateModal')
        ->set('description', 'Đi học đúng giờ')
        ->set('type', 'plus')
        ->set('status', 'applied')
        ->set('point_value', 'h')
        ->call('saveRegulation')
        ->assertHasErrors(['point_value' => 'integer']);
});

test('regulations can be reordered from the list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.regulation.view',
        'management.regulation.update',
    ]);

    $firstRegulation = Regulation::factory()->create([
        'ordering' => 1,
        'description' => 'Đi lễ đúng giờ',
    ]);

    $secondRegulation = Regulation::factory()->create([
        'ordering' => 2,
        'description' => 'Đi học đúng giờ',
    ]);

    $thirdRegulation = Regulation::factory()->create([
        'ordering' => 3,
        'description' => 'Tham gia sinh hoạt đầy đủ',
    ]);

    $this->actingAs($user);

    Livewire::test(RegulationList::class)
        ->call('sortRegulation', $thirdRegulation->id, 0)
        ->assertHasNoErrors();

    expect($thirdRegulation->fresh()->ordering)->toBe(1);
    expect($firstRegulation->fresh()->ordering)->toBe(2);
    expect($secondRegulation->fresh()->ordering)->toBe(3);
});
