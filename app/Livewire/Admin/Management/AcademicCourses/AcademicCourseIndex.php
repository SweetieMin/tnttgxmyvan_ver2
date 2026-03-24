<?php

namespace App\Livewire\Admin\Management\AcademicCourses;

use App\Models\AcademicYear;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Lớp giáo lý - ngành')]
class AcademicCourseIndex extends Component
{
    public string $search = '';

    public int|string|null $selectedAcademicYear = '';

    public int $perPage = 15;

    public function mount(): void
    {
        $this->selectedAcademicYear = $this->defaultAcademicYearId() ?? '';
    }

    public function resetFilter(): void
    {
        $this->reset(['search', 'perPage', 'selectedAcademicYear']);

        $this->selectedAcademicYear = $this->defaultAcademicYearId() ?? '';
        $this->perPage = 15;
    }

    public function openCreateModal(): void
    {
        $this->dispatch('open-create-academic-course-modal', academicYearId: $this->resolvedAcademicYearId());
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-courses.index', [
            'academicYearOptions' => $this->academicYearOptions(),
        ]);
    }

    /**
     * @return array<int, array{value:int,label:string}>
     */
    protected function academicYearOptions(): array
    {
        return $this->academicYears()
            ->map(fn (AcademicYear $academicYear): array => [
                'value' => (int) $academicYear->id,
                'label' => $academicYear->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, AcademicYear>
     */
    protected function academicYears(): Collection
    {
        return AcademicYear::query()
            ->orderByRaw("case status_academic when 'ongoing' then 0 when 'upcoming' then 1 when 'finished' then 2 else 3 end")
            ->orderByDesc('name')
            ->get();
    }

    protected function defaultAcademicYearId(): ?int
    {
        /** @var AcademicYear|null $academicYear */
        $academicYear = AcademicYear::query()
            ->where('status_academic', 'ongoing')
            ->latest('id')
            ->first();

        if ($academicYear !== null) {
            return (int) $academicYear->id;
        }

        $academicYearId = AcademicYear::query()->latest('id')->value('id');

        return $academicYearId !== null ? (int) $academicYearId : null;
    }

    protected function resolvedAcademicYearId(): ?int
    {
        if ($this->selectedAcademicYear === '' || $this->selectedAcademicYear === null) {
            return null;
        }

        return (int) $this->selectedAcademicYear;
    }
}
