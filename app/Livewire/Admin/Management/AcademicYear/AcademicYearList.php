<?php

namespace App\Livewire\Admin\Management\AcademicYear;

use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AcademicYearList extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 15;

    public function mount(string $search = '', int $perPage = 15): void
    {
        $this->search = $search;
        $this->perPage = $perPage;
    }

    #[On('academic-year-saved')]
    #[On('academic-year-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-year.academic-year-list', [
            'academicYears' => $this->academicYearRepository()->paginateForAdmin($this->search, $this->perPage),
        ]);
    }

    protected function academicYearRepository(): AcademicYearRepositoryInterface
    {
        return app(AcademicYearRepositoryInterface::class);
    }
}
