<?php

namespace App\Livewire\Admin\Arrangement\SectorAssignments;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\AcademicYearSectorStaff;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SectorAssignmentList extends Component
{
    public int $academicYearId;

    /**
     * @var Collection<int, array{
     *     sector_name: string,
     *     course_names: array<int, string>,
     *     sector_head: ?string,
     *     vice_sector_head: ?string,
     *     leaders: array<int, string>
     * }>|null
     */
    protected ?Collection $sectorAssignmentsCache = null;

    #[On('sector-assignment-saved')]
    public function refreshAssignments(): void
    {
        $this->sectorAssignmentsCache = null;
    }

    /**
     * @return Collection<int, array{
     *     sector_name: string,
     *     course_names: array<int, string>,
     *     sector_head: ?string,
     *     vice_sector_head: ?string,
     *     leaders: array<int, string>
     * }>
     */
    public function sectorAssignments(): Collection
    {
        $sectorNames = AcademicCourse::query()
            ->where('academic_year_id', $this->academicYearId)
            ->pluck('sector_name')
            ->filter()
            ->unique()
            ->values();

        $courseNamesBySector = AcademicCourse::query()
            ->where('academic_year_id', $this->academicYearId)
            ->whereIn('sector_name', $sectorNames)
            ->orderBy('ordering')
            ->orderBy('id')
            ->get(['sector_name', 'course_name'])
            ->groupBy('sector_name');

        $assignmentsBySector = AcademicYearSectorStaff::query()
            ->with('user:id,last_name,name')
            ->where('academic_year_id', $this->academicYearId)
            ->whereIn('sector_name', $sectorNames)
            ->get()
            ->groupBy('sector_name');

        return $this->sectorAssignmentsCache ??= $sectorNames->map(function (string $sectorName) use ($assignmentsBySector, $courseNamesBySector): array {
            $assignments = $assignmentsBySector->get($sectorName, collect());

            return [
                'sector_name' => $sectorName,
                'course_names' => $courseNamesBySector
                    ->get($sectorName, collect())
                    ->pluck('course_name')
                    ->values()
                    ->all(),
                'sector_head' => $assignments
                    ->firstWhere('assignment_type', 'sector_leader')
                    ?->user
                    ?->full_name,
                'vice_sector_head' => $assignments
                    ->firstWhere('assignment_type', 'assistant_sector_leader')
                    ?->user
                    ?->full_name,
                'leaders' => $assignments
                    ->where('assignment_type', 'leader')
                    ->pluck('user.full_name')
                    ->filter()
                    ->sort()
                    ->values()
                    ->all(),
            ];
        });
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    public function overviewStats(): array
    {
        $sectorAssignments = $this->sectorAssignments();

        return [
            [
                'label' => __('Sectors'),
                'value' => $sectorAssignments->count(),
            ],
            [
                'label' => __('Sectors with sector heads'),
                'value' => $sectorAssignments
                    ->filter(fn (array $sector): bool => $sector['sector_head'] !== null)
                    ->count(),
            ],
            [
                'label' => __('Sectors missing leaders'),
                'value' => $sectorAssignments
                    ->filter(fn (array $sector): bool => $sector['sector_head'] === null && $sector['vice_sector_head'] === null && $sector['leaders'] === [])
                    ->count(),
            ],
        ];
    }

    public function editLeaderAssignment(string $sectorName): void
    {
        if (! $this->canUpdateAssignments()) {
            return;
        }

        $this->dispatch('edit-sector-leader-assignment', sectorName: $sectorName);
    }

    public function canUpdateAssignments(): bool
    {
        return Auth::user()?->can('arrangement.sector-assignment.update') ?? false;
    }

    public function academicYear(): ?AcademicYear
    {
        return AcademicYear::query()->find($this->academicYearId);
    }

    public function render(): View
    {
        return view('livewire.admin.arrangement.sector-assignments.sector-assignment-list', [
            'academicYear' => $this->academicYear(),
            'sectorAssignments' => $this->sectorAssignments(),
            'overviewStats' => $this->overviewStats(),
        ]);
    }
}
