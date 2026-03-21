<?php

namespace App\Livewire\Admin\Management\Programs;

use App\Repositories\Contracts\ProgramRepositoryInterface;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function mount(string $search = '', int $perPage = 15): void
    {
        $this->search = $search;
        $this->perPage = $perPage;
    }

    #[On('program-saved')]
    #[On('program-deleted')]
    #[On('program-reordered')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function sortProgram(int $item, int $position): void
    {
        abort_unless((bool) Auth::user()?->can('management.program.update'), 403);

        $absolutePosition = (($this->getPage() - 1) * $this->perPage) + $position;

        $this->programRepository()->reorder($item, $absolutePosition);

        Flux::toast(
            text: __('Program order updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('program-reordered');
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function render(): View
    {
        return view('livewire.admin.management.programs.program-list', [
            'programs' => $this->programRepository()->paginateForAdmin($this->search, $this->perPage),
        ]);
    }

    protected function programRepository(): ProgramRepositoryInterface
    {
        return app(ProgramRepositoryInterface::class);
    }
}
