<?php

namespace App\Livewire\Admin\Management\SectorAssignments;

use App\Concerns\ActivityLogs\LogsAcademicYearSectorAssignmentActivity;
use App\Foundation\PersonnelDirectory;
use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\AcademicYearSectorStaff;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class SectorAssignmentActions extends Component
{
    use LogsAcademicYearSectorAssignmentActivity;

    public int $academicYearId;

    public bool $showLeaderAssignmentModal = false;

    public string $editingSectorName = '';

    /**
     * @var array<int, string>
     */
    public array $editingSectorCourseNames = [];

    public int|string|null $sectorHeadUserId = null;

    public int|string|null $viceSectorHeadUserId = null;

    /**
     * @var array<int, int|string>
     */
    public array $leaderIds = [];

    #[On('edit-sector-leader-assignment')]
    public function openLeaderAssignmentModal(string $sectorName): void
    {
        $this->ensureCan('arrangement.sector-assignment.update');

        $academicYear = AcademicYear::query()->findOrFail($this->academicYearId);

        $assignments = AcademicYearSectorStaff::query()
            ->with('user:id,last_name,name')
            ->where('academic_year_id', $academicYear->id)
            ->where('sector_name', $sectorName)
            ->get();

        $this->resetLeaderAssignmentState();
        $this->editingSectorName = $sectorName;
        $this->editingSectorCourseNames = AcademicCourse::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('sector_name', $sectorName)
            ->orderBy('ordering')
            ->orderBy('id')
            ->pluck('course_name')
            ->values()
            ->all();
        $this->sectorHeadUserId = $assignments->firstWhere('assignment_type', 'sector_leader')?->user_id;
        $this->viceSectorHeadUserId = $assignments->firstWhere('assignment_type', 'assistant_sector_leader')?->user_id;
        $this->leaderIds = $assignments
            ->where('assignment_type', 'leader')
            ->pluck('user_id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
        $this->showLeaderAssignmentModal = true;
    }

    public function saveLeaderAssignments(): void
    {
        $this->ensureCan('arrangement.sector-assignment.update');

        $this->sectorHeadUserId = $this->normalizeNullableUserId($this->sectorHeadUserId);
        $this->viceSectorHeadUserId = $this->normalizeNullableUserId($this->viceSectorHeadUserId);
        $this->leaderIds = $this->normalizeUserIds($this->leaderIds);

        $validated = $this->validate([
            'sectorHeadUserId' => ['nullable', 'integer', 'exists:users,id'],
            'viceSectorHeadUserId' => ['nullable', 'integer', 'exists:users,id'],
            'leaderIds' => ['array'],
            'leaderIds.*' => ['integer', 'exists:users,id'],
        ]);

        $academicYear = AcademicYear::query()->findOrFail($this->academicYearId);

        $this->syncSectorAssignments(
            $academicYear,
            $this->editingSectorName,
            $validated['sectorHeadUserId'],
            $validated['viceSectorHeadUserId'],
            $validated['leaderIds'],
        );

        Flux::toast(
            text: __('Sector leader assignments updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('sector-assignment-saved');
        $this->closeLeaderAssignmentModal();
    }

    public function closeLeaderAssignmentModal(): void
    {
        $this->showLeaderAssignmentModal = false;
        $this->resetLeaderAssignmentState();
    }

    /**
     * @return array<int, string>
     */
    public function leaderOptions(): array
    {
        return $this->personnelOptionsForGroup('leaders');
    }

    public function render(): View
    {
        return view('livewire.admin.management.sector-assignments.sector-assignment-actions', [
            'leaderOptions' => $this->leaderOptions(),
        ]);
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless(Auth::user()?->can($permission) ?? false, 403);
    }

    protected function resetLeaderAssignmentState(): void
    {
        $this->editingSectorName = '';
        $this->editingSectorCourseNames = [];
        $this->sectorHeadUserId = null;
        $this->viceSectorHeadUserId = null;
        $this->leaderIds = [];
        $this->resetErrorBag([
            'sectorHeadUserId',
            'viceSectorHeadUserId',
            'leaderIds',
            'leaderIds.*',
        ]);
    }

    /**
     * @param  array<int, mixed>  $leaderUserIds
     */
    protected function syncSectorAssignments(
        AcademicYear $academicYear,
        string $sectorName,
        mixed $sectorHeadUserId,
        mixed $viceSectorHeadUserId,
        array $leaderUserIds,
    ): void {
        $existingAssignments = AcademicYearSectorStaff::query()
            ->with('user:id,last_name,name')
            ->where('academic_year_id', $academicYear->id)
            ->where('sector_name', $sectorName)
            ->whereIn('assignment_type', ['sector_leader', 'assistant_sector_leader', 'leader'])
            ->get();

        $normalizedSectorHeadUserId = is_numeric($sectorHeadUserId) ? (int) $sectorHeadUserId : null;
        $normalizedViceSectorHeadUserId = is_numeric($viceSectorHeadUserId) ? (int) $viceSectorHeadUserId : null;

        if (
            $normalizedSectorHeadUserId !== null
            && $normalizedViceSectorHeadUserId !== null
            && $normalizedSectorHeadUserId === $normalizedViceSectorHeadUserId
        ) {
            $normalizedViceSectorHeadUserId = null;
        }

        $normalizedLeaderUserIds = collect($leaderUserIds)
            ->filter(fn (mixed $userId): bool => is_numeric($userId))
            ->map(fn (mixed $userId): int => (int) $userId)
            ->reject(function (int $userId) use ($normalizedSectorHeadUserId, $normalizedViceSectorHeadUserId): bool {
                return $userId === $normalizedSectorHeadUserId || $userId === $normalizedViceSectorHeadUserId;
            })
            ->unique()
            ->values()
            ->all();

        $previousAssignmentSnapshot = $this->sectorAssignmentSnapshot($existingAssignments);
        $currentAssignmentSnapshot = $this->currentSectorAssignmentSnapshot(
            $normalizedSectorHeadUserId,
            $normalizedViceSectorHeadUserId,
            $normalizedLeaderUserIds,
        );

        if (
            $previousAssignmentSnapshot['sector_head'] === $currentAssignmentSnapshot['sector_head']
            && $previousAssignmentSnapshot['vice_sector_head'] === $currentAssignmentSnapshot['vice_sector_head']
            && $previousAssignmentSnapshot['leaders'] === $currentAssignmentSnapshot['leaders']
        ) {
            return;
        }

        AcademicYearSectorStaff::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('sector_name', $sectorName)
            ->whereIn('assignment_type', ['sector_leader', 'assistant_sector_leader', 'leader'])
            ->delete();

        $assignments = [];

        if ($normalizedSectorHeadUserId !== null) {
            $assignments[] = [
                'academic_year_id' => $academicYear->id,
                'sector_name' => $sectorName,
                'user_id' => $normalizedSectorHeadUserId,
                'assignment_type' => 'sector_leader',
                'assigned_by' => Auth::id(),
            ];
        }

        if ($normalizedViceSectorHeadUserId !== null) {
            $assignments[] = [
                'academic_year_id' => $academicYear->id,
                'sector_name' => $sectorName,
                'user_id' => $normalizedViceSectorHeadUserId,
                'assignment_type' => 'assistant_sector_leader',
                'assigned_by' => Auth::id(),
            ];
        }

        foreach ($normalizedLeaderUserIds as $leaderUserId) {
            $assignments[] = [
                'academic_year_id' => $academicYear->id,
                'sector_name' => $sectorName,
                'user_id' => $leaderUserId,
                'assignment_type' => 'leader',
                'assigned_by' => Auth::id(),
            ];
        }

        if ($assignments !== []) {
            $academicYear->sectorStaffAssignments()->createMany($assignments);
        }

        $this->logAcademicYearSectorAssignments(
            $academicYear,
            $sectorName,
            $previousAssignmentSnapshot['sector_head'],
            $currentAssignmentSnapshot['sector_head'],
            $previousAssignmentSnapshot['vice_sector_head'],
            $currentAssignmentSnapshot['vice_sector_head'],
            $previousAssignmentSnapshot['leaders'],
            $currentAssignmentSnapshot['leaders'],
        );
    }

    /**
     * @return array<int, string>
     */
    protected function personnelOptionsForGroup(string $group): array
    {
        $roleNames = $this->personnelDirectory()->roleNamesForGroup($group);

        if ($roleNames === []) {
            return [];
        }

        return User::query()
            ->with('roles:id,name')
            ->whereHas('roles', function (Builder $query) use ($roleNames): void {
                $query->whereIn('name', $roleNames);
            })
            ->orderBy('last_name')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (User $user): string => sprintf(
                '%05d|%s|%s',
                $this->contextRoleIdForGroup($user, $roleNames),
                mb_strtolower($user->last_name),
                mb_strtolower($user->name),
            ))
            ->mapWithKeys(fn (User $user): array => [
                $user->id => $user->full_name.' - '.$this->groupRoleLabel($user, $roleNames),
            ])
            ->all();
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    protected function contextRoleIdForGroup(User $user, array $roleNames): int
    {
        return (int) ($user->roles
            ->filter(fn (Role $role): bool => in_array($role->name, $roleNames, true))
            ->pluck('id')
            ->min() ?? PHP_INT_MAX);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    protected function groupRoleLabel(User $user, array $roleNames): string
    {
        return $user->roles
            ->filter(fn (Role $role): bool => in_array($role->name, $roleNames, true))
            ->sortBy('id')
            ->pluck('name')
            ->first() ?? __('Unknown');
    }

    protected function personnelDirectory(): PersonnelDirectory
    {
        return app(PersonnelDirectory::class);
    }

    protected function normalizeNullableUserId(mixed $userId): ?int
    {
        return is_numeric($userId) ? (int) $userId : null;
    }

    /**
     * @param  array<int, mixed>  $userIds
     * @return array<int, int>
     */
    protected function normalizeUserIds(array $userIds): array
    {
        return collect($userIds)
            ->filter(fn (mixed $userId): bool => is_numeric($userId))
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
    }
}
