<?php

use App\Livewire\Admin\Access\Permissions\Index as PermissionIndex;
use App\Livewire\Admin\Access\Roles\Index as RoleIndex;
use App\Livewire\Admin\Management\AcademicYear\Index as AcademicYearIndex;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('access')->name('access.')->group(function () {
            Route::livewire('roles', RoleIndex::class)
                ->middleware('can:viewAny,'.Role::class)
                ->name('roles');

            Route::livewire('permissions', PermissionIndex::class)
                ->middleware('can:viewAny,'.Permission::class)
                ->name('permissions');
        });

        Route::prefix('management')->name('management.')->group(function () {
            Route::livewire('academic-years', AcademicYearIndex::class)
                ->middleware('permission:management.academic-year.view')
                ->name('academic-years');
        });

    });
});

require __DIR__.'/settings.php';
