<?php

namespace App\Livewire\Admin\Access\Roles;

use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class RoleList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function mount(string $search = '', int $perPage = 15): void
    {
        $this->search = $search;
        $this->perPage = $perPage;
    }

    #[On('role-saved')]
    #[On('role-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-950/20">
                <div class="space-y-3">
                    <div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                    <div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                    <div class="h-10 animate-pulse rounded-xl bg-zinc-100 dark:bg-zinc-800"></div>
                </div>
            </div>
        </div>
        HTML;
    }

    public function render(): View
    {
        return view('livewire.admin.access.roles.role-list', [
            'roles' => $this->roleRepository()->paginateForAdmin($this->search, $this->perPage),
        ]);
    }

    protected function roleRepository(): RoleRepositoryInterface
    {
        return app(RoleRepositoryInterface::class);
    }
}
