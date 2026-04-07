<?php

namespace App\Concerns\ActivityLogs;

use App\Models\AcademicCourse;
use App\Models\AcademicCourseStaff;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait LogsAcademicCourseAssignmentActivity
{
    /**
     * @param  Collection<int, AcademicCourseStaff>  $assignments
     * @return array{primary: ?string, supporting: array<int, string>}
     */
    protected function assignmentSnapshot(
        Collection $assignments,
        string $primaryAssignmentType,
        string $assistantAssignmentType,
    ): array {
        return [
            'primary' => $this->assignmentUserName(
                $assignments->firstWhere('assignment_type', $primaryAssignmentType)?->user,
            ),
            'supporting' => $this->assignmentUserNames(
                $assignments->where('assignment_type', $assistantAssignmentType),
            ),
        ];
    }

    /**
     * @param  array<int, int>  $assistantUserIds
     * @return array{primary: ?string, supporting: array<int, string>}
     */
    protected function currentAssignmentSnapshot(?int $primaryUserId, array $assistantUserIds): array
    {
        $assistantUsers = $assistantUserIds !== []
            ? User::query()
                ->whereKey($assistantUserIds)
                ->get(['id', 'last_name', 'name'])
            : collect();

        return [
            'primary' => $this->assignmentUserName(
                $primaryUserId !== null ? User::query()->find($primaryUserId) : null,
            ),
            'supporting' => $this->userNames($assistantUsers),
        ];
    }

    /**
     * @param  Collection<int, AcademicCourseStaff>  $assignments
     * @return array<int, string>
     */
    protected function assignmentUserNames(Collection $assignments): array
    {
        return $this->userNames($assignments->pluck('user')->filter());
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

    protected function assignmentUserName(?User $user): ?string
    {
        return $user?->full_name;
    }

    /**
     * @param  array<int, string>  $previousAssistantUserNames
     * @param  array<int, string>  $currentAssistantUserNames
     */
    protected function logAcademicCourseAssignments(
        AcademicCourse $record,
        string $assignmentGroupKey,
        ?string $previousPrimaryUserName,
        ?string $currentPrimaryUserName,
        array $previousAssistantUserNames,
        array $currentAssistantUserNames,
    ): void {
        $assignmentGroupLabel = $assignmentGroupKey === 'catechists'
            ? __('Catechists')
            : __('Leaders');

        activity('academic_courses')
            ->performedOn($record)
            ->causedBy(Auth::user())
            ->event('updated')
            ->withProperties([
                'attributes' => [
                    'assignment_group' => $assignmentGroupLabel,
                    'academic_course' => $record->course_name,
                    'sector' => $record->sector_name,
                    'primary_before' => $previousPrimaryUserName,
                    'primary_after' => $currentPrimaryUserName,
                    'supporting_before' => $previousAssistantUserNames,
                    'supporting_after' => $currentAssistantUserNames,
                ],
            ])
            ->log($assignmentGroupKey === 'catechists'
                ? __('Updated catechist assignments')
                : __('Updated leader assignments'));
    }
}
