<?php

namespace App\Providers;

use App\Foundation\PersonnelDirectory;
use App\Foundation\SidebarNavigation;
use App\Foundation\SiteSettings;
use App\Models\Permission;
use App\Models\Role;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Repositories\Contracts\AcademicCourseRepositoryInterface;
use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\ProgramRepositoryInterface;
use App\Repositories\Contracts\RegulationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Eloquent\AcademicCourseRepository;
use App\Repositories\Eloquent\AcademicYearRepository;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\PermissionRepository;
use App\Repositories\Eloquent\ProgramRepository;
use App\Repositories\Eloquent\RegulationRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\TransactionRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View as ViewInstance;
use Livewire\Blaze\Blaze;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PersonnelDirectory::class);
        $this->app->singleton(SiteSettings::class);

        $this->app->bind(AcademicYearRepositoryInterface::class, AcademicYearRepository::class);
        $this->app->bind(AcademicCourseRepositoryInterface::class, AcademicCourseRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProgramRepositoryInterface::class, ProgramRepository::class);
        $this->app->bind(RegulationRepositoryInterface::class, RegulationRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Blaze::debug();

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        View::composer('*', function (ViewInstance $view): void {
            /** @var array<string, mixed> $sharedSiteSettings */
            $sharedSiteSettings = once(fn (): array => app(SiteSettings::class)->shared());

            /** @var array<string, mixed> $sharedSidebarData */
            $sharedSidebarData = once(function (): array {
                $currentUser = Auth::user()?->loadMissing('details');

                return [
                    'sidebarNavigation' => app(SidebarNavigation::class)->for($currentUser),
                    'sidebarUserName' => $currentUser?->full_name ?? $currentUser?->name ?? '',
                    'sidebarUserEmail' => $currentUser?->email ?? '',
                    'sidebarUserPicture' => data_get($currentUser, 'details.picture'),
                ];
            });

            $view->with(array_merge($sharedSiteSettings, $sharedSidebarData));
        });

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
