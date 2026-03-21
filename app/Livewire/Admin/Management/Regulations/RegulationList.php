<?php

namespace App\Livewire\Admin\Management\Regulations;

use App\Repositories\Contracts\RegulationRepositoryInterface;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class RegulationList extends Component
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

    #[On('regulation-saved')]
    #[On('regulation-deleted')]
    #[On('regulation-reordered')]
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

    public function sortRegulation(int $item, int $position): void
    {
        abort_unless((bool) Auth::user()?->can('management.regulation.update'), 403);

        $absolutePosition = (($this->getPage() - 1) * $this->perPage) + $position;

        $this->regulationRepository()->reorder($item, $absolutePosition);

        Flux::toast(
            text: __('Regulation order updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('regulation-reordered');
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function render(): View
    {
        return view('livewire.admin.management.regulations.regulation-list', [
            'regulations' => $this->regulationRepository()->paginateForAdmin($this->search, $this->perPage),
        ]);
    }

    protected function regulationRepository(): RegulationRepositoryInterface
    {
        return app(RegulationRepositoryInterface::class);
    }
}
