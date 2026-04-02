<?php

use App\Livewire\Admin\Access\Permissions\PermissionIndex;
use App\Livewire\Admin\Access\Roles\RoleIndex;
use App\Livewire\Admin\Finance\Categories\CategoryAnalytics;
use App\Livewire\Admin\Finance\Categories\CategoryIndex;
use App\Livewire\Admin\Finance\Transactions\TransactionIndex;
use App\Livewire\Admin\Management\AcademicCourses\AcademicCourseIndex;
use App\Livewire\Admin\Management\AcademicYear\AcademicYearIndex;
use App\Livewire\Admin\Management\ActivityPoints\ActivityPointIndex;
use App\Livewire\Admin\Management\AttendanceCheckins\AttendanceCheckinIndex;
use App\Livewire\Admin\Management\AttendanceSchedules\AttendanceScheduleIndex;
use App\Livewire\Admin\Management\Enrollments\EnrollmentIndex;
use App\Livewire\Admin\Management\Gradebooks\GradebookIndex;
use App\Livewire\Admin\Management\Programs\ProgramIndex;
use App\Livewire\Admin\Management\Promotions\PromotionIndex;
use App\Livewire\Admin\Management\Regulations\RegulationIndex;
use App\Livewire\Admin\Management\SectorAssignments\SectorAssignmentIndex;
use App\Livewire\Admin\Personnel\Catechists\CatechistIndex;
use App\Livewire\Admin\Personnel\Children\ChildIndex;
use App\Livewire\Admin\Personnel\DeletedUsers\DeletedUserIndex;
use App\Livewire\Admin\Personnel\Directors\DirectorIndex;
use App\Livewire\Admin\Personnel\Leaders\LeaderIndex;
use App\Livewire\Admin\Personnel\UserProfileEditor;
use App\Livewire\Admin\Personnel\Users\UserIndex;
use App\Livewire\Front\ProfileShow;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::livewire('profile/{token}', ProfileShow::class)->name('front.profile.show');

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

            Route::livewire('academic-courses', AcademicCourseIndex::class)
                ->middleware('permission:management.academic-course.view')
                ->name('academic-courses');

            Route::livewire('enrollments', EnrollmentIndex::class)
                ->middleware('permission:management.enrollment.view')
                ->name('enrollments');

            Route::livewire('gradebooks', GradebookIndex::class)
                ->middleware('permission:management.gradebook.view')
                ->name('gradebooks');

            Route::livewire('sector-assignments', SectorAssignmentIndex::class)
                ->middleware('permission:management.sector-assignment.view')
                ->name('sector-assignments');

            Route::livewire('attendance-schedules', AttendanceScheduleIndex::class)
                ->middleware('permission:management.attendance-schedule.view')
                ->name('attendance-schedules');

            Route::livewire('attendance-checkins', AttendanceCheckinIndex::class)
                ->middleware('permission:management.attendance-checkin.view')
                ->name('attendance-checkins');

            Route::livewire('activity-points', ActivityPointIndex::class)
                ->middleware('permission:management.activity-point.view')
                ->name('activity-points');

            Route::livewire('promotions', PromotionIndex::class)
                ->middleware('permission:management.promotion.view')
                ->name('promotions');

            Route::livewire('programs', ProgramIndex::class)
                ->middleware('permission:management.program.view')
                ->name('programs');

            Route::livewire('regulations', RegulationIndex::class)
                ->middleware('permission:management.regulation.view')
                ->name('regulations');
        });

        Route::prefix('finance')->name('finance.')->group(function () {
            Route::livewire('categories/analytics', CategoryAnalytics::class)
                ->middleware('permission:finance.category.view')
                ->name('categories.analytics');

            Route::livewire('categories', CategoryIndex::class)
                ->middleware('permission:finance.category.view')
                ->name('categories');

            Route::livewire('transactions', TransactionIndex::class)
                ->middleware('permission:finance.transaction.view')
                ->name('transactions');

        });

        Route::prefix('personnel')->name('personnel.')->group(function () {
            Route::livewire('users', UserIndex::class)
                ->middleware('permission:personnel.user.view')
                ->name('users');

            Route::livewire('directors', DirectorIndex::class)
                ->middleware('permission:personnel.director.view')
                ->name('directors');

            Route::livewire('catechists', CatechistIndex::class)
                ->middleware('permission:personnel.catechist.view')
                ->name('catechists');

            Route::livewire('leaders', LeaderIndex::class)
                ->middleware('permission:personnel.leader.view')
                ->name('leaders');

            Route::livewire('children', ChildIndex::class)
                ->middleware('permission:personnel.child.view')
                ->name('children');

            Route::livewire('deleted-users', DeletedUserIndex::class)
                ->middleware('permission:personnel.deleted.view')
                ->name('deleted-users');

            Route::livewire('{group}/create', UserProfileEditor::class)
                ->middleware('permission:personnel.user.create|personnel.director.create|personnel.catechist.create|personnel.leader.create|personnel.child.create')
                ->name('create');

            Route::livewire('{group}/users/{user}/edit', UserProfileEditor::class)
                ->middleware('permission:personnel.user.update|personnel.director.update|personnel.catechist.update|personnel.leader.update|personnel.child.update')
                ->name('users.edit');

            Route::get('{group}/users/{user}', function (string $group, User $user) {
                return redirect()->route('admin.personnel.users.edit', [
                    'group' => $group,
                    'user' => $user,
                ]);
            })
                ->middleware('permission:personnel.user.update|personnel.director.update|personnel.catechist.update|personnel.leader.update|personnel.child.update');
        });

    });
});

require __DIR__.'/settings.php';
