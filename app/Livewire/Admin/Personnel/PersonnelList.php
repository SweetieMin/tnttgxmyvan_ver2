<?php

namespace App\Livewire\Admin\Personnel;

use App\Foundation\PersonnelDirectory;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class PersonnelList extends Component
{
    use WithoutUrlPagination;
    use WithPagination;

    public string $group = 'users';

    public string $search = '';

    public int $perPage = 15;

    public string $selectedStatus = '';

    public bool $showDeleteModal = false;

    public ?int $deletingUserId = null;

    public function mount(string $group = 'users', string $search = '', int $perPage = 15, string $selectedStatus = ''): void
    {
        abort_unless($this->directory()->hasGroup($group), 404);

        $this->group = $group;
        $this->search = $search;
        $this->perPage = $perPage;
        $this->selectedStatus = $selectedStatus !== ''
            ? $selectedStatus
            : $this->defaultSelectedStatus();
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

    public function confirmDeleteUser(int $userId): void
    {
        $user = User::query()->with('roles')->findOrFail($userId);
        abort_unless($this->canDeleteUser($user), 403);

        $this->deletingUserId = $user->id;
        $this->showDeleteModal = true;
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

    public function editRoute(User $user): string
    {
        return route('admin.personnel.users.edit', [
            'group' => $this->group,
            'user' => $user,
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->directory()->isDeletedUsersPage($this->group), function (Builder $query): void {
                $query->onlyTrashed();
            })
            ->with([
                'details',
                'religious_profile',
                'roles',
            ])
            ->where(function (Builder $query): void {
                $query->when($this->directory()->isAllUsersPage($this->group), function (Builder $allUsersQuery): void {
                    $allUsersQuery->doesntHave('roles');
                });

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

        return $roleNames[0] ?? '—';
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

    protected function defaultSelectedStatus(): string
    {
        return $this->group === 'children' ? 'in_course' : '';
    }

    protected function directory(): PersonnelDirectory
    {
        return app(PersonnelDirectory::class);
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
