<?php

namespace App\Livewire\Admin\Finance\Categories;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithoutUrlPagination;
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function mount(string $search = '', int $perPage = 15): void
    {
        $this->search = $search;
        $this->perPage = $perPage;
    }

    #[On('category-saved')]
    #[On('category-deleted')]
    #[On('category-reordered')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function sortCategory(int $item, int $position): void
    {
        abort_unless((bool) Auth::user()?->can('finance.category.update'), 403);

        $absolutePosition = (($this->getPage() - 1) * $this->perPage) + $position;

        $this->categoryRepository()->reorder($item, $absolutePosition);

        Flux::toast(
            text: __('Category order updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('category-reordered');
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function render(): View
    {
        return view('livewire.admin.finance.categories.category-list', [
            'categories' => $this->categoryRepository()->paginateForAdmin($this->search, $this->perPage),
        ]);
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return app(CategoryRepositoryInterface::class);
    }
}
