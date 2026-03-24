<?php

namespace App\Livewire\Admin\Management\AcademicCourses;

use App\Repositories\Contracts\AcademicCourseRepositoryInterface;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class AcademicCourseList extends Component
{
    use WithoutUrlPagination;
    use WithPagination;

    public string $search = '';

    public int|string|null $selectedAcademicYear = '';

    public int $perPage = 15;

    public function mount(string $search = '', int|string|null $selectedAcademicYear = '', int $perPage = 15): void
    {
        $this->search = $search;
        $this->selectedAcademicYear = $selectedAcademicYear;
        $this->perPage = $perPage;
    }

    #[On('academic-course-saved')]
    #[On('academic-course-deleted')]
    #[On('academic-course-reordered')]
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

    public function updatedSelectedAcademicYear(): void
    {
        $this->resetPage();
    }

    public function sortAcademicCourse(int $item, int $position): void
    {
        abort_unless((bool) Auth::user()?->can('management.academic-course.update'), 403);

        $absolutePosition = (($this->getPage() - 1) * $this->perPage) + $position;

        $this->academicCourseRepository()->reorder(
            $item,
            $absolutePosition,
            $this->resolvedAcademicYearId(),
        );

        Flux::toast(
            text: __('Catechism - sector class order updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('academic-course-reordered');
    }

    public function placeholder(): string
    {
        return view('components.placeholder.table')->render();
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-courses.academic-course-list', [
            'academicCourses' => $this->academicCourseRepository()->paginateForAdmin(
                $this->search,
                $this->perPage,
                $this->resolvedAcademicYearId(),
            ),
        ]);
    }

    protected function academicCourseRepository(): AcademicCourseRepositoryInterface
    {
        return app(AcademicCourseRepositoryInterface::class);
    }

    protected function resolvedAcademicYearId(): ?int
    {
        if ($this->selectedAcademicYear === '' || $this->selectedAcademicYear === null) {
            return null;
        }

        return (int) $this->selectedAcademicYear;
    }
}
