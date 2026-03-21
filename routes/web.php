<?php

use App\Livewire\Admin\Access\Permissions\PermissionIndex;
use App\Livewire\Admin\Access\Roles\RoleIndex;
use App\Livewire\Admin\Finance\Transactions\TransactionIndex;
use App\Livewire\Admin\Management\AcademicYear\AcademicYearIndex;
use App\Livewire\Admin\Management\Programs\ProgramIndex;
use App\Livewire\Admin\Management\Regulations\RegulationIndex;
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

            Route::livewire('programs', ProgramIndex::class)
                ->middleware('permission:management.program.view')
                ->name('programs');

            Route::livewire('regulations', RegulationIndex::class)
                ->middleware('permission:management.regulation.view')
                ->name('regulations');
        });

        Route::prefix('finance')->name('finance.')->group(function () {

            Route::livewire('transactions', TransactionIndex::class)
                ->middleware('permission:finance.transaction.view')
                ->name('transactions');

        });

    });
});

require __DIR__.'/settings.php';
