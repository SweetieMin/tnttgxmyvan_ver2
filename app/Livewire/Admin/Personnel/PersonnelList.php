<?php

namespace App\Livewire\Admin\Personnel;

use App\Foundation\PersonnelDirectory;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\BadgePreviewPngExporter;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lab404\Impersonate\Services\ImpersonateManager;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersonnelList extends Component
{
    use WithoutUrlPagination;
    use WithPagination;

    public string $group = 'users';

    public string $search = '';

    public int $perPage = 15;

    public string $selectedStatus = '';

    public string $selectedRole = '';

    public bool $showDeleteModal = false;

    public ?int $deletingUserId = null;

    public bool $showRestoreModal = false;

    public ?int $restoringUserId = null;

    public bool $showForceDeleteModal = false;

    public ?int $forceDeletingUserId = null;

    public bool $showBadgePreviewModal = false;

    public ?int $previewingBadgeUserId = null;

    public function mount(
        string $group = 'users',
        string $search = '',
        int $perPage = 15,
        string $selectedStatus = '',
        string $selectedRole = '',
    ): void {
        abort_unless($this->directory()->hasGroup($group), 404);

        $this->group = $group;
        $this->search = $search;
        $this->perPage = $perPage;
        $this->selectedStatus = $selectedStatus !== ''
            ? $selectedStatus
            : $this->defaultSelectedStatus();
        $this->selectedRole = $selectedRole;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedRole(): void
    {
        $this->resetPage();
    }

    public function confirmDeleteUser(int $userId): void
    {
        $user = User::query()->with('roles')->findOrFail($userId);
        abort_unless($this->canDeleteUser($user), 403);

        $this->deletingUserId = $user->id;
        $this->showDeleteModal = true;
    }

    public function confirmRestoreUser(int $userId): void
    {
        $user = User::onlyTrashed()->with('roles')->findOrFail($userId);
        abort_unless($this->canRestoreUser($user), 403);

        $this->restoringUserId = $user->id;
        $this->showRestoreModal = true;
    }

    public function confirmForceDeleteUser(int $userId): void
    {
        $user = User::onlyTrashed()->with('roles')->findOrFail($userId);
        abort_unless($this->canForceDeleteUser($user), 403);

        $this->forceDeletingUserId = $user->id;
        $this->showForceDeleteModal = true;
    }

    public function deleteUser(): void
    {
        $user = User::query()->with('roles')->findOrFail($this->deletingUserId);
        abort_unless($this->canDeleteUser($user), 403);

        $user->delete();

        Flux::toast(
            text: __('Personnel profile deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->resetPage();
    }

    public function restoreUser(): void
    {
        $user = User::onlyTrashed()->with('roles')->findOrFail($this->restoringUserId);
        abort_unless($this->canRestoreUser($user), 403);

        $user->restore();

        Flux::toast(
            text: __('Personnel profile restored successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->showRestoreModal = false;
        $this->restoringUserId = null;
        $this->resetPage();
    }

    public function forceDeleteUser(): void
    {
        $user = User::onlyTrashed()->with('roles')->findOrFail($this->forceDeletingUserId);
        abort_unless($this->canForceDeleteUser($user), 403);

        $user->forceDelete();

        Flux::toast(
            text: __('Personnel profile permanently deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->showForceDeleteModal = false;
        $this->forceDeletingUserId = null;
        $this->resetPage();
    }

    public function previewBadgeUser(int $userId): void
    {
        $user = User::query()->with('details')->findOrFail($userId);
        abort_unless($this->canExportBadgeUser($user), 403);

        $this->previewingBadgeUserId = $user->id;
        $this->showBadgePreviewModal = true;
    }

    public function canUpdateUser(User $user): bool
    {
        $permission = $this->directory()->updatePermissionForGroup($this->group);

        if ($permission === null || ! auth()->user()?->can($permission)) {
            return false;
        }

        return $this->isVisibleUser($user);
    }

    public function canDeleteUser(User $user): bool
    {
        if ($this->directory()->isDeletedUsersPage($this->group)) {
            return false;
        }

        if ($this->directory()->isAllUsersPage($this->group)) {
            return (bool) auth()->user()?->can($this->directory()->deletePermissionForGroup('users'))
                && $this->isVisibleUser($user);
        }

        return collect($this->groupKeysForUser($user))
            ->every(fn (string $groupKey): bool => (bool) auth()->user()?->can(
                $this->directory()->deletePermissionForGroup($groupKey) ?? '',
            ));
    }

    public function canRestoreUser(User $user): bool
    {
        if (! $this->directory()->isDeletedUsersPage($this->group) || ! $user->trashed()) {
            return false;
        }

        if ($user->roles->isEmpty()) {
            return (bool) auth()->user()?->can($this->directory()->updatePermissionForGroup('users') ?? '');
        }

        return $this->isVisibleUser($user)
            && collect($this->groupKeysForUser($user))
                ->every(fn (string $groupKey): bool => (bool) auth()->user()?->can(
                    $this->directory()->updatePermissionForGroup($groupKey) ?? '',
                ));
    }

    public function canForceDeleteUser(User $user): bool
    {
        if (! $this->directory()->isDeletedUsersPage($this->group) || ! $user->trashed()) {
            return false;
        }

        if ($user->roles->isEmpty()) {
            return (bool) auth()->user()?->can($this->directory()->deletePermissionForGroup('users') ?? '');
        }

        return $this->isVisibleUser($user)
            && collect($this->groupKeysForUser($user))
                ->every(fn (string $groupKey): bool => (bool) auth()->user()?->can(
                    $this->directory()->deletePermissionForGroup($groupKey) ?? '',
                ));
    }

    public function canExportBadgeUser(User $user): bool
    {
        if (! $this->directory()->isAllUsersPage($this->group) || $user->trashed()) {
            return false;
        }

        return $this->isVisibleUser($user)
            && $this->hasExportableBadgeAvatar($user)
            && (bool) auth()->user()?->can($this->directory()->permissionForGroup('users'));
    }

    public function canImpersonateUser(User $user): bool
    {
        $currentUser = Auth::user();

        if (! $currentUser instanceof User) {
            return false;
        }

        if (! $this->directory()->isAllUsersPage($this->group) || $user->trashed()) {
            return false;
        }

        if ($currentUser->isImpersonated() || $currentUser->is($user)) {
            return false;
        }

        return $currentUser->canImpersonate()
            && $this->isVisibleUser($user)
            && $user->canBeImpersonated();
    }

    public function impersonateUser(int $userId): void
    {
        $user = User::query()->with('roles')->findOrFail($userId);
        abort_unless($this->canImpersonateUser($user), 403);

        /** @var User $currentUser */
        $currentUser = Auth::user();
        abort_unless($currentUser->impersonate($user), 403);

        Flux::toast(
            text: __('You are now impersonating :name.', ['name' => $user->full_name]),
            heading: __('Success'),
            variant: 'success',
        );

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function editRoute(User $user): string
    {
        return route('admin.personnel.users.edit', [
            'group' => $this->group,
            'user' => $user,
        ]);
    }

    public function exportBadgeUser(int $userId): StreamedResponse
    {
        $user = User::query()->with('details')->findOrFail($userId);
        abort_unless($this->canExportBadgeUser($user), 403);

        $png = app(BadgePreviewPngExporter::class)->render(
            user: $user,
            blocks: $this->badgeTemplateBlocks(),
            options: $this->badgeTemplateOptions(),
        );

        return response()->streamDownload(
            callback: static function () use ($png): void {
                echo $png;
            },
            name: Str::slug($user->username ?: $user->full_name, '-').'-badge.png',
            headers: ['Content-Type' => 'image/png'],
        );
    }

    public function exportPreviewBadgeUser(): StreamedResponse
    {
        abort_if($this->previewingBadgeUserId === null, 404);

        return $this->exportBadgeUser($this->previewingBadgeUserId);
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function users(): LengthAwarePaginator
    {
        $query = User::query()
            ->when($this->directory()->isDeletedUsersPage($this->group), function (Builder $query): void {
                $query->onlyTrashed();
            })
            ->with([
                'details',
                'religious_profile',
                'roles',
            ])
            ->where(function (Builder $query): void {
                $query->when(
                    $this->directory()->isAllUsersPage($this->group) || $this->directory()->isDeletedUsersPage($this->group),
                    function (Builder $allUsersQuery): void {
                        $allUsersQuery->doesntHave('roles');
                    },
                );

                $query->orWhereHas('roles', function (Builder $roleQuery): void {
                    $roleQuery->whereIn('name', $this->visibleRoleNames());
                });
            })
            ->when($this->search !== '', function ($query): void {
                $search = trim($this->search);

                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('christian_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhereHas('details', function ($detailsQuery) use ($search): void {
                            $detailsQuery->where('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($this->group === 'children', function ($query): void {
                if ($this->selectedStatus === 'graduated') {
                    $query->whereHas('religious_profile', function ($religiousProfileQuery): void {
                        $religiousProfileQuery->where('status_religious', 'graduated');
                    });

                    return;
                }

                $query->where(function ($childrenQuery): void {
                    $childrenQuery->whereDoesntHave('religious_profile')
                        ->orWhereHas('religious_profile', function ($religiousProfileQuery): void {
                            $religiousProfileQuery->where('status_religious', 'in_course');
                        });
                });
            })
            ->when($this->selectedRole !== '', function (Builder $query): void {
                $query->whereHas('roles', function (Builder $roleQuery): void {
                    $roleQuery->where('name', $this->selectedRole);
                });
            });

        if ($this->directory()->isGroupPage($this->group) || $this->directory()->isAllUsersPage($this->group)) {
            $sortedUsers = $this->sortUsersByRoleId(
                $query
                    ->orderBy('name')
                    ->orderBy('last_name')
                    ->get(),
            );

            $currentPage = Paginator::resolveCurrentPage('page');
            $items = $sortedUsers->forPage($currentPage, $this->perPage)->values();

            return new Paginator(
                $items,
                $sortedUsers->count(),
                $this->perPage,
                $currentPage,
                [
                    'pageName' => 'page',
                    'path' => request()->url(),
                    'query' => request()->query(),
                ],
            );
        }

        return $query
            ->orderBy('name')
            ->orderBy('last_name')
            ->paginate($this->perPage);
    }

    public function mainRole(User $user): string
    {
        $roleNames = $user->roles
            ->pluck('name')
            ->filter(fn (mixed $roleName): bool => is_string($roleName) && $roleName !== '')
            ->values()
            ->all();

        if ($this->directory()->isGroupPage($this->group)) {
            $contextRoleNames = array_values(array_intersect(
                $this->directory()->roleNamesForGroup($this->group),
                $roleNames,
            ));

            if ($contextRoleNames !== []) {
                return implode(', ', $contextRoleNames);
            }
        }

        return $user->roles
            ->sortBy('id')
            ->pluck('name')
            ->first() ?? '—';
    }

    public function userStatusLabel(User $user): string
    {
        return match ($user->status_login) {
            'active' => __('Active'),
            'locked' => __('Locked'),
            'inactive' => __('Inactive'),
            default => __('Unknown'),
        };
    }

    public function userStatusColor(User $user): string
    {
        return match ($user->status_login) {
            'active' => 'emerald',
            'locked' => 'amber',
            'inactive' => 'zinc',
            default => 'zinc',
        };
    }

    public function childStudyStatusLabel(User $user): string
    {
        return match (data_get($user, 'religious_profile.status_religious', 'in_course')) {
            'graduated' => __('Completed'),
            default => __('Studying'),
        };
    }

    public function childStudyStatusColor(User $user): string
    {
        return match (data_get($user, 'religious_profile.status_religious', 'in_course')) {
            'graduated' => 'sky',
            default => 'violet',
        };
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function previewBadgeUserModel(): ?User
    {
        if ($this->previewingBadgeUserId === null) {
            return null;
        }

        return User::query()->with('details')->find($this->previewingBadgeUserId);
    }

    public function previewBadgeAvatarUrl(): string
    {
        return data_get($this->previewBadgeUserModel(), 'details.picture')
            ?: asset('/storage/images/users/default-avatar.png');
    }

    public function previewBadgeChristianName(): string
    {
        return (string) data_get($this->previewBadgeUserModel(), 'christian_name', '');
    }

    public function previewBadgeFullName(): string
    {
        return (string) data_get($this->previewBadgeUserModel(), 'full_name', '');
    }

    public function previewBadgeQrCodeSvg(): ?string
    {
        $user = $this->previewBadgeUserModel();

        if ($user === null || blank($user->token)) {
            return null;
        }

        return $user->getTokenQrCode();
    }

    public function previewBadgeFaviconUrl(): string
    {
        $faviconPath = (string) $this->settingValue('branding.favicon');
        $logoPath = (string) $this->settingValue('branding.logo');

        return $faviconPath !== ''
            ? $this->resolveBrandingImageUrl($faviconPath)
            : ($logoPath !== '' ? $this->resolveBrandingImageUrl($logoPath) : asset('/storage/images/users/default-avatar.png'));
    }

    public function badgeBackgroundColor(): string
    {
        return (string) $this->settingValue('badge.background_color', '#fff3cb');
    }

    public function badgeNamePanelColor(): string
    {
        return (string) $this->settingValue('badge.name_panel_color', '#efd089');
    }

    /**
     * @return array<int, string>
     */
    protected function groupKeysForUser(User $user): array
    {
        return $this->directory()->groupsForRoles($user->roles->pluck('name')->all());
    }

    protected function isVisibleUser(User $user): bool
    {
        if ($this->directory()->isAllUsersPage($this->group) && $user->roles->isEmpty()) {
            return true;
        }

        return array_intersect(
            $user->roles->pluck('name')->all(),
            $this->visibleRoleNames(),
        ) !== [];
    }

    /**
     * @return array<string, array{x:int,y:int,w:int,h:int}>
     */
    protected function badgeTemplateBlocks(): array
    {
        $defaults = [
            'logo' => ['x' => 4, 'y' => 4, 'w' => 12, 'h' => 12],
            'heading' => ['x' => 16, 'y' => 3, 'w' => 72, 'h' => 15],
            'qr' => ['x' => 25, 'y' => 16, 'w' => 50, 'h' => 28],
            'name_panel' => ['x' => 3, 'y' => 74, 'w' => 94, 'h' => 20],
            'avatar' => ['x' => 12, 'y' => 45, 'w' => 76, 'h' => 40],
            'christian_name' => ['x' => 18, 'y' => 84, 'w' => 64, 'h' => 6],
            'full_name' => ['x' => 8, 'y' => 89, 'w' => 84, 'h' => 9],
        ];

        $decoded = json_decode((string) $this->settingValue('badge.layout'), true);

        if (! is_array($decoded)) {
            return $defaults;
        }

        foreach ($defaults as $key => $defaultBlock) {
            $block = is_array($decoded[$key] ?? null) ? $decoded[$key] : [];

            $defaults[$key] = [
                'x' => max(0, min(100, (int) round((float) ($block['x'] ?? $defaultBlock['x'])))),
                'y' => max(0, min(100, (int) round((float) ($block['y'] ?? $defaultBlock['y'])))),
                'w' => max(6, min(100, (int) round((float) ($block['w'] ?? $defaultBlock['w'])))),
                'h' => max(6, min(100, (int) round((float) ($block['h'] ?? $defaultBlock['h'])))),
            ];
        }

        return $defaults;
    }

    /**
     * @return array{title:string,subtitle:string,background_color:string,name_panel_color:string,favicon_path:?string}
     */
    protected function badgeTemplateOptions(): array
    {
        return [
            'title' => (string) ($this->settingValue('badge.title') ?? ''),
            'subtitle' => (string) ($this->settingValue('badge.subtitle') ?? ''),
            'background_color' => (string) ($this->settingValue('badge.background_color') ?? '#fff3cb'),
            'name_panel_color' => (string) ($this->settingValue('badge.name_panel_color') ?? '#efd089'),
            'favicon_path' => $this->settingValue('branding.favicon') ?: $this->settingValue('branding.logo'),
        ];
    }

    protected function settingValue(string $key, ?string $default = null): ?string
    {
        return Setting::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    protected function hasExportableBadgeAvatar(User $user): bool
    {
        $user->loadMissing('details');

        $picture = (string) ($user->details?->getRawOriginal('picture') ?? '');

        if ($picture === '') {
            return false;
        }

        return Storage::disk('public')->exists('images/users/'.$picture);
    }

    protected function resolveBrandingImageUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        if (Str::startsWith($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }

    protected function defaultSelectedStatus(): string
    {
        return $this->group === 'children' ? 'in_course' : '';
    }

    protected function directory(): PersonnelDirectory
    {
        return app(PersonnelDirectory::class);
    }

    /**
     * @param  Collection<int, User>  $users
     * @return Collection<int, User>
     */
    protected function sortUsersByRoleId(Collection $users): Collection
    {
        return $users
            ->sortBy(fn (User $user): string => sprintf(
                '%05d|%s|%s',
                $this->contextRoleId($user),
                Str::lower($user->name),
                Str::lower($user->last_name),
            ))
            ->values();
    }

    protected function contextRoleId(User $user): int
    {
        return (int) ($this->rolesForCurrentContext($user)
            ->pluck('id')
            ->min() ?? PHP_INT_MAX);
    }

    /**
     * @return Collection<int, Role>
     */
    protected function rolesForCurrentContext(User $user): Collection
    {
        $roles = $user->roles;

        if (! $this->directory()->isGroupPage($this->group)) {
            return $roles;
        }

        return $roles->filter(fn (mixed $role): bool => $role instanceof Role
            && in_array($role->name, $this->directory()->roleNamesForGroup($this->group), true));
    }

    protected function impersonateManager(): ImpersonateManager
    {
        return app(ImpersonateManager::class);
    }

    /**
     * @return array<int, string>
     */
    protected function visibleRoleNames(): array
    {
        return $this->directory()->manageableRoleNamesFor(
            auth()->user(),
            $this->directory()->isGroupPage($this->group) ? $this->group : null,
        );
    }

    public function render(): View
    {
        return view('livewire.admin.personnel.personnel-list', [
            'users' => $this->users(),
        ]);
    }
}
