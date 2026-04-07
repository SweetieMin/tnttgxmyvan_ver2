<?php

namespace App\Livewire\Admin\Arrangement\SectorAssignments;

use App\Models\AcademicYear;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Phân công ngành')]
class SectorAssignmentIndex extends Component
{
    public int|string $academicYearId = '';

    public function mount(): void
    {
        $this->academicYearId = $this->defaultAcademicYearId() ?? '';
    }

    /**
     * @return Collection<int, AcademicYear>
     */
    public function academicYears(): Collection
    {
        return AcademicYear::query()
            ->orderByRaw("case status_academic when 'ongoing' then 0 when 'upcoming' then 1 when 'finished' then 2 else 3 end")
            ->orderByDesc('name')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.admin.arrangement.sector-assignments.index', [
            'academicYears' => $this->academicYears(),
        ]);
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
}
