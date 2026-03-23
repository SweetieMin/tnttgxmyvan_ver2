<?php

use App\Livewire\Admin\Finance\Categories\CategoryActions;
use App\Livewire\Admin\Finance\Categories\CategoryList;
use App\Models\Category;
use App\Models\Permission;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'finance.category.view',
        'finance.category.create',
        'finance.category.update',
        'finance.category.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the categories page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('finance.category.view');

    $response = $this->actingAs($user)->get(route('admin.finance.categories'));

    $response->assertOk()
        ->assertSeeText(__('Categories'));
});

test('categories can be created updated and deleted from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'finance.category.view',
        'finance.category.create',
        'finance.category.update',
        'finance.category.delete',
    ]);

    $this->actingAs($user);

    Livewire::test(CategoryActions::class)
        ->call('openCreateModal')
        ->set('name', 'Le Phuc Sinh')
        ->set('description', 'Sinh hoat mua phuc sinh')
        ->set('is_active', true)
        ->call('saveCategory')
        ->assertHasNoErrors();

    $category = Category::query()->where('name', 'Le Phuc Sinh')->firstOrFail();

    Livewire::test(CategoryActions::class)
        ->call('openEditModal', $category->id)
        ->set('description', 'Da cap nhat')
        ->set('is_active', false)
        ->call('saveCategory')
        ->assertHasNoErrors();

    expect($category->fresh()->description)->toBe('Da cap nhat')
        ->and($category->fresh()->is_active)->toBeFalse();

    Livewire::test(CategoryActions::class)
        ->call('confirmDeleteCategory', $category->id)
        ->call('deleteCategory')
        ->assertHasNoErrors();

    expect(Category::query()->whereKey($category->id)->exists())->toBeFalse();
});

test('categories can be reordered from the list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'finance.category.view',
        'finance.category.update',
    ]);

    $firstCategory = Category::factory()->create([
        'ordering' => 1,
        'name' => 'A',
    ]);

    $secondCategory = Category::factory()->create([
        'ordering' => 2,
        'name' => 'B',
    ]);

    $thirdCategory = Category::factory()->create([
        'ordering' => 3,
        'name' => 'C',
    ]);

    $this->actingAs($user);

    Livewire::test(CategoryList::class)
        ->call('sortCategory', $thirdCategory->id, 0)
        ->assertHasNoErrors();

    expect($thirdCategory->fresh()->ordering)->toBe(1)
        ->and($firstCategory->fresh()->ordering)->toBe(2)
        ->and($secondCategory->fresh()->ordering)->toBe(3);
});

test('category save button only appears after the form changes on edit', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'finance.category.create',
        'finance.category.update',
    ]);

    $category = Category::factory()->create();

    $this->actingAs($user);

    Livewire::test(CategoryActions::class)
        ->call('openCreateModal')
        ->call('shouldShowSaveCategoryButton')
        ->assertReturned(true);

    Livewire::test(CategoryActions::class)
        ->call('openEditModal', $category->id)
        ->call('shouldShowSaveCategoryButton')
        ->assertReturned(false)
        ->set('name', $category->name.' moi')
        ->call('shouldShowSaveCategoryButton')
        ->assertReturned(true);
});
