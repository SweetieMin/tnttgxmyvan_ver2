<?php

use App\Livewire\Admin\Management\Programs\ProgramActions;
use App\Livewire\Admin\Management\Programs\ProgramList;
use App\Models\Permission;
use App\Models\Program;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'management.program.view',
        'management.program.create',
        'management.program.update',
        'management.program.delete',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the programs page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('management.program.view');

    $response = $this->actingAs($user)->get(route('admin.management.programs'));

    $response->assertOk()
        ->assertSeeText(__('Programs'));
});

test('programs can be created updated and deleted from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.program.view',
        'management.program.create',
        'management.program.update',
        'management.program.delete',
    ]);

    $this->actingAs($user);

    Livewire::test(ProgramActions::class)
        ->call('openCreateModal')
        ->set('course', 'Khai Tâm Đặc Biệt')
        ->set('sector', 'Tiền Ấu Đặc Biệt')
        ->call('saveProgram')
        ->assertHasNoErrors();

    $program = Program::query()->where('course', 'Khai Tâm Đặc Biệt')->firstOrFail();

    Livewire::test(ProgramActions::class)
        ->call('openEditModal', $program->id)
        ->set('sector', 'Tiền Ấu Nâng Cao')
        ->call('saveProgram')
        ->assertHasNoErrors();

    expect($program->fresh()->sector)->toBe('Tiền Ấu Nâng Cao');

    Livewire::test(ProgramActions::class)
        ->call('confirmDeleteProgram', $program->id)
        ->call('deleteProgram')
        ->assertHasNoErrors();

    expect(Program::query()->whereKey($program->id)->exists())->toBeFalse();
});

test('program save button only appears after the form changes on edit', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.program.create',
        'management.program.update',
    ]);

    $program = Program::factory()->create();

    $this->actingAs($user);

    Livewire::test(ProgramActions::class)
        ->call('openCreateModal')
        ->call('shouldShowSaveProgramButton')
        ->assertReturned(true);

    Livewire::test(ProgramActions::class)
        ->call('openEditModal', $program->id)
        ->call('shouldShowSaveProgramButton')
        ->assertReturned(false)
        ->set('course', $program->course.' Mới')
        ->call('shouldShowSaveProgramButton')
        ->assertReturned(true);
});

test('programs can be reordered from the list', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.program.view',
        'management.program.update',
    ]);

    $firstProgram = Program::factory()->create([
        'ordering' => 1,
        'course' => 'Khai Tâm 1',
        'sector' => 'Tiền Ấu 1',
    ]);

    $secondProgram = Program::factory()->create([
        'ordering' => 2,
        'course' => 'Khai Tâm 2',
        'sector' => 'Tiền Ấu 2',
    ]);

    $thirdProgram = Program::factory()->create([
        'ordering' => 3,
        'course' => 'Khai Tâm 3',
        'sector' => 'Tiền Ấu 3',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(ProgramList::class)
        ->assertCanSeeTableRecords([
            $firstProgram,
            $secondProgram,
            $thirdProgram,
        ], inOrder: true)
        ->call('reorderTable', [
            $thirdProgram->id,
            $firstProgram->id,
            $secondProgram->id,
        ])
        ->assertHasNoErrors();

    expect($component->instance()->getTable()->isReorderable())->toBeTrue();

    expect($thirdProgram->fresh()->ordering)->toBe(1);
    expect($firstProgram->fresh()->ordering)->toBe(2);
    expect($secondProgram->fresh()->ordering)->toBe(3);
});

test('program filament table supports searching and record actions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'management.program.view',
        'management.program.update',
        'management.program.delete',
    ]);

    $alphaProgram = Program::factory()->create([
        'ordering' => 1,
        'course' => 'Khai Tâm Căn Bản',
        'sector' => 'Tiền Ấu 1',
    ]);

    $betaProgram = Program::factory()->create([
        'ordering' => 2,
        'course' => 'Nghĩa Sĩ Nâng Cao',
        'sector' => 'Thiếu Nhi 2',
    ]);

    $this->actingAs($user);

    Livewire::test(ProgramList::class)
        ->assertCanSeeTableRecords([
            $alphaProgram,
            $betaProgram,
        ], inOrder: true)
        ->assertTableActionExists('edit', record: $alphaProgram)
        ->assertTableActionExists('delete', record: $alphaProgram)
        ->searchTable('Thiếu Nhi 2')
        ->assertCanSeeTableRecords([$betaProgram])
        ->assertCanNotSeeTableRecords([$alphaProgram]);
});
