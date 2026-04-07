<?php

namespace App\Concerns\ActivityLogs;

use App\Models\AcademicYear;
use App\Models\AcademicYearSectorStaff;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait LogsAcademicYearSectorAssignmentActivity
{
    /**
     * @param  Collection<int, AcademicYearSectorStaff>  $assignments
     * @return array{sector_head: ?string, vice_sector_head: ?string, leaders: array<int, string>}
     */
    protected function sectorAssignmentSnapshot(Collection $assignments): array
    {
        return [
            'sector_head' => $this->sectorAssignmentUserName(
                $assignments->firstWhere('assignment_type', 'sector_leader')?->user,
            ),
            'vice_sector_head' => $this->sectorAssignmentUserName(
                $assignments->firstWhere('assignment_type', 'assistant_sector_leader')?->user,
            ),
            'leaders' => $this->sectorAssignmentUserNames(
                $assignments->where('assignment_type', 'leader'),
            ),
        ];
    }

    /**
     * @param  array<int, int>  $leaderUserIds
     * @return array{sector_head: ?string, vice_sector_head: ?string, leaders: array<int, string>}
     */
    protected function currentSectorAssignmentSnapshot(?int $sectorHeadUserId, ?int $viceSectorHeadUserId, array $leaderUserIds): array
    {
        $leaders = $leaderUserIds !== []
            ? User::query()
                ->whereKey($leaderUserIds)
                ->get(['id', 'last_name', 'name'])
            : collect();

        return [
            'sector_head' => $this->sectorAssignmentUserName(
                $sectorHeadUserId !== null ? User::query()->find($sectorHeadUserId) : null,
            ),
            'vice_sector_head' => $this->sectorAssignmentUserName(
                $viceSectorHeadUserId !== null ? User::query()->find($viceSectorHeadUserId) : null,
            ),
            'leaders' => $this->userNames($leaders),
        ];
    }

    /**
     * @param  Collection<int, AcademicYearSectorStaff>  $assignments
     * @return array<int, string>
     */
    protected function sectorAssignmentUserNames(Collection $assignments): array
    {
        return $this->userNames($assignments->pluck('user')->filter());
    }

    protected function sectorAssignmentUserName(?User $user): ?string
    {
        return $user?->full_name;
    }

    /**
     * @param  Collection<int, User>  $users
     * @return array<int, string>
     */
    protected function userNames(Collection $users): array
    {
        return $users
            ->map(fn (User $user): string => $user->full_name)
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $previousLeaderUserNames
     * @param  array<int, string>  $currentLeaderUserNames
     */
    protected function logAcademicYearSectorAssignments(
        AcademicYear $academicYear,
        string $sectorName,
        ?string $previousSectorHeadUserName,
        ?string $currentSectorHeadUserName,
        ?string $previousViceSectorHeadUserName,
        ?string $currentViceSectorHeadUserName,
        array $previousLeaderUserNames,
        array $currentLeaderUserNames,
    ): void {
        activity('sector_assignments')
            ->performedOn($academicYear)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'attributes' => [
                    'academic_year' => $academicYear->name,
                    'sector' => $sectorName,
                    'sector_head_before' => $previousSectorHeadUserName,
                    'sector_head_after' => $currentSectorHeadUserName,
                    'vice_sector_head_before' => $previousViceSectorHeadUserName,
                    'vice_sector_head_after' => $currentViceSectorHeadUserName,
                    'leaders_before' => $previousLeaderUserNames,
                    'leaders_after' => $currentLeaderUserNames,
                ],
            ])
            ->log(__('Updated sector leader assignments'));
    }
}
