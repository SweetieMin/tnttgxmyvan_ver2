<?php

use App\Livewire\Admin\Access\Permissions\PermissionList;
use App\Livewire\Admin\Access\Roles\RoleList;
use App\Livewire\Admin\Management\AcademicYear\AcademicYearList;
use App\Livewire\Admin\Management\Programs\ProgramList;
use App\Livewire\Admin\Management\Regulations\RegulationList;
use Livewire\Livewire;

dataset('paginated-list-components', [
    'roles' => RoleList::class,
    'permissions' => PermissionList::class,
    'academic years' => AcademicYearList::class,
    'programs' => ProgramList::class,
    'regulations' => RegulationList::class,
]);

it('resets pagination when the search filter changes', function (string $component): void {
    Livewire::test($component)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'keyword')
        ->assertSet('paginators.page', 1);
})->with('paginated-list-components');

it('resets pagination when the per page filter changes', function (string $component): void {
    Livewire::test($component)
        ->call('gotoPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('perPage', 25)
        ->assertSet('paginators.page', 1);
})->with('paginated-list-components');
