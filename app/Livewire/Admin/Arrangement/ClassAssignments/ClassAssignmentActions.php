<?php

namespace App\Livewire\Admin\Arrangement\ClassAssignments;

use App\Concerns\ActivityLogs\LogsAcademicCourseAssignmentActivity;
use App\Foundation\PersonnelDirectory;
use App\Models\AcademicCourse;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ClassAssignmentActions extends Component
{
    use LogsAcademicCourseAssignmentActivity;

    public int $academicYearId;

    public bool $showCatechistAssignmentModal = false;

    public ?int $editingAcademicCourseId = null;

    public string $editingAcademicCourseLabel = '';

    public int|string|null $primaryCatechistId = null;

    /**
     * @var array<int, int|string>
     */
    public array $assistantCatechistIds = [];

    #[On('edit-class-catechist-assignment')]
    public function openCatechistAssignmentModal(int $academicCourseId): void
    {
        $this->ensureCan('arrangement.class-assignment.update');

        $course = AcademicCourse::query()
            ->with([
                'staffAssignments' => fn ($query) => $query
                    ->whereIn('assignment_type', ['catechist', 'assistant_catechist'])
                    ->with('user:id,last_name,name'),
            ])
            ->where('academic_year_id', $this->academicYearId)
            ->findOrFail($academicCourseId);

        $this->resetCatechistAssignmentState();
        $this->editingAcademicCourseId = (int) $course->id;
        $this->editingAcademicCourseLabel = $course->course_name.' - '.$course->sector_name;
        $this->primaryCatechistId = $course->staffAssignments
            ->firstWhere('assignment_type', 'catechist')
            ?->user_id;
        $this->assistantCatechistIds = $course->staffAssignments
            ->where('assignment_type', 'assistant_catechist')
            ->pluck('user_id')
            ->map(fn (mixed $userId): int => (int) $userId)
            ->values()
            ->all();
        $this->showCatechistAssignmentModal = true;
    }

    public function saveCatechistAssignments(): void
    {
        $this->ensureCan('arrangement.class-assignment.update');

        $this->primaryCatechistId = $this->normalizeNullableUserId($this->primaryCatechistId);
        $this->assistantCatechistIds = $this->normalizeUserIds($this->assistantCatechistIds);

        $validated = $this->validate([
            'primaryCatechistId' => ['nullable', 'integer', 'exists:users,id'],
            'assistantCatechistIds' => ['array'],
            'assistantCatechistIds.*' => ['integer', 'exists:users,id'],
        ]);

        $course = AcademicCourse::query()
            ->with('staffAssignments.user:id,last_name,name')
            ->findOrFail($this->editingAcademicCourseId);

        $this->syncAcademicCourseAssignments(
            $course,
            primaryAssignmentType: 'catechist',
            assistantAssignmentType: 'assistant_catechist',
            primaryUserId: $validated['primaryCatechistId'],
            assistantUserIds: $validated['assistantCatechistIds'],
        );

        Flux::toast(
            text: __('Catechist assignments updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('class-assignment-saved');
        $this->closeCatechistAssignmentModal();
    }

    public function closeCatechistAssignmentModal(): void
    {
        $this->showCatechistAssignmentModal = false;
        $this->resetCatechistAssignmentState();
    }

    /**
     * @return array<int, string>
     */
    public function catechistOptions(): array
    {
        return $this->personnelOptionsForGroup('catechists');
    }

    public function render(): View
    {
        return view('livewire.admin.arrangement.class-assignments.class-assignment-actions', [
            'catechistOptions' => $this->catechistOptions(),
        ]);
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless(Auth::user()?->can($permission) ?? false, 403);
    }

    protected function resetCatechistAssignmentState(): void
    {
        $this->editingAcademicCourseId = null;
        $this->editingAcademicCourseLabel = '';
        $this->primaryCatechistId = null;
        $this->assistantCatechistIds = [];
        $this->resetErrorBag([
            'primaryCatechistId',
            'assistantCatechistIds',
            'assistantCatechistIds.*',
        ]);
    }

    /**
     * @param  array<int, mixed>  $assistantUserIds
     */
    protected function syncAcademicCourseAssignments(
        AcademicCourse $record,
        string $primaryAssignmentType,
        string $assistantAssignmentType,
        mixed $primaryUserId,
        array $assistantUserIds,
    ): void {
        $existingAssignments = $record->staffAssignments()
            ->with('user:id,last_name,name')
            ->whereIn('assignment_type', [$primaryAssignmentType, $assistantAssignmentType])
            ->get();

        $normalizedPrimaryUserId = is_numeric($primaryUserId) ? (int) $primaryUserId : null;
        $normalizedAssistantUserIds = collect($assistantUserIds)
            ->filter(fn (mixed $userId): bool => is_numeric($userId))
            ->map(fn (mixed $userId): int => (int) $userId)
            ->when(
                $normalizedPrimaryUserId !== null,
                fn (Collection $userIds): Collection => $userIds->reject(fn (int $userId): bool => $userId === $normalizedPrimaryUserId),
            )
            ->unique()
            ->values()
            ->all();

        $previousAssignmentSnapshot = $this->assignmentSnapshot(
            $existingAssignments,
            $primaryAssignmentType,
            $assistantAssignmentType,
        );
        $currentAssignmentSnapshot = $this->currentAssignmentSnapshot(
            $normalizedPrimaryUserId,
            $normalizedAssistantUserIds,
        );

        if (
            $previousAssignmentSnapshot['primary'] === $currentAssignmentSnapshot['primary']
            && $previousAssignmentSnapshot['supporting'] === $currentAssignmentSnapshot['supporting']
        ) {
            return;
        }

        $record->staffAssignments()
            ->whereIn('assignment_type', [$primaryAssignmentType, $assistantAssignmentType])
            ->delete();

        $assignments = [];

        if ($normalizedPrimaryUserId !== null) {
            $assignments[] = [
                'user_id' => $normalizedPrimaryUserId,
                'assignment_type' => $primaryAssignmentType,
                'is_primary' => true,
                'assigned_by' => Auth::id(),
            ];
        }

        foreach ($normalizedAssistantUserIds as $assistantUserId) {
            $assignments[] = [
                'user_id' => $assistantUserId,
                'assignment_type' => $assistantAssignmentType,
                'is_primary' => false,
                'assigned_by' => Auth::id(),
            ];
        }

        if ($assignments !== []) {
            $record->staffAssignments()->createMany($assignments);
        }

        $this->logAcademicCourseAssignments(
            $record,
            'catechists',
            $previousAssignmentSnapshot['primary'],
            $currentAssignmentSnapshot['primary'],
            $previousAssignmentSnapshot['supporting'],
            $currentAssignmentSnapshot['supporting'],
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
