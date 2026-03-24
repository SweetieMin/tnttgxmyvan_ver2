<?php

namespace App\Livewire\Admin\Management\AcademicCourses;

use App\Models\AcademicCourse;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Repositories\Contracts\AcademicCourseRepositoryInterface;
use App\Validation\Admin\Management\AcademicCourseRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class AcademicCourseActions extends Component
{
    public bool $showAcademicCourseModal = false;

    public bool $showDeleteModal = false;

    public bool $isDuplicatingAcademicCourse = false;

    public ?int $editingAcademicCourseId = null;

    public ?int $deletingAcademicCourseId = null;

    #[Validate]
    public int|string $academic_year_id = '';

    #[Validate]
    public int|string $program_id = '';

    #[Validate]
    public int|string $ordering = '';

    #[Validate]
    public string $course_name = '';

    #[Validate]
    public string $sector_name = '';

    #[Validate]
    public string $catechism_avg_score = '5.00';

    #[Validate]
    public string $catechism_training_score = '5.00';

    #[Validate]
    public int $activity_score = 150;

    #[Validate]
    public bool $is_active = true;

    #[Locked]
    public array $originalAcademicCourseState = [];

    #[On('open-create-academic-course-modal')]
    public function openCreateModal(?int $academicYearId = null): void
    {
        $this->ensureCan('management.academic-course.create');
        $this->resetForm();

        if ($academicYearId !== null) {
            $this->academic_year_id = $academicYearId;
            $this->fillAcademicYearDefaults($academicYearId);
        }

        $this->showAcademicCourseModal = true;
    }

    #[On('edit-academic-course')]
    public function openEditModal(int $academicCourseId): void
    {
        $this->ensureCan('management.academic-course.update');

        $academicCourse = $this->academicCourseRepository()->find($academicCourseId);

        $this->editingAcademicCourseId = (int) $academicCourse->id;
        $this->academic_year_id = $academicCourse->academic_year_id;
        $this->program_id = $academicCourse->program_id;
        $this->ordering = $academicCourse->ordering;
        $this->course_name = $academicCourse->course_name;
        $this->sector_name = $academicCourse->sector_name;
        $this->catechism_avg_score = (string) $academicCourse->catechism_avg_score;
        $this->catechism_training_score = (string) $academicCourse->catechism_training_score;
        $this->activity_score = (int) $academicCourse->activity_score;
        $this->is_active = (bool) $academicCourse->is_active;
        $this->isDuplicatingAcademicCourse = false;
        $this->syncOriginalAcademicCourseState();
        $this->showAcademicCourseModal = true;
    }

    #[On('duplicate-academic-course')]
    public function openDuplicateModal(int $academicCourseId): void
    {
        $this->ensureCan('management.academic-course.create');

        $academicCourse = $this->academicCourseRepository()->find($academicCourseId);

        $this->resetForm();
        $this->academic_year_id = $academicCourse->academic_year_id;
        $this->program_id = $academicCourse->program_id;
        $this->ordering = $this->academicCourseRepository()->nextOrderingForAcademicYear($academicCourse->academic_year_id);
        $this->course_name = $this->duplicateValueForAcademicYear($academicCourse->course_name, 'course_name', $academicCourse->academic_year_id);
        $this->sector_name = $this->duplicateValueForAcademicYear($academicCourse->sector_name, 'sector_name', $academicCourse->academic_year_id);
        $this->catechism_avg_score = (string) $academicCourse->catechism_avg_score;
        $this->catechism_training_score = (string) $academicCourse->catechism_training_score;
        $this->activity_score = (int) $academicCourse->activity_score;
        $this->is_active = (bool) $academicCourse->is_active;
        $this->isDuplicatingAcademicCourse = true;
        $this->syncOriginalAcademicCourseState();
        $this->showAcademicCourseModal = true;
    }

    public function updatedProgramId(): void
    {
        if ($this->program_id === '' || $this->program_id === null) {
            return;
        }

        /** @var Program|null $program */
        $program = Program::query()->find($this->program_id);

        if ($program === null) {
            return;
        }

        $this->ordering = $program->ordering;
        $this->course_name = $program->course;
        $this->sector_name = $program->sector;
    }

    public function updatedAcademicYearId(): void
    {
        if ($this->academic_year_id === '' || $this->academic_year_id === null) {
            return;
        }

        $this->fillAcademicYearDefaults((int) $this->academic_year_id);
    }

    public function saveAcademicCourse(): void
    {
        $this->ensureCan($this->editingAcademicCourseId ? 'management.academic-course.update' : 'management.academic-course.create');

        $validated = $this->validate();

        try {
            $this->academicCourseRepository()->save($validated, $this->editingAcademicCourseId);
        } catch (Throwable $exception) {
            $this->addError('course_name', __('Academic course save failed.'));

            Flux::toast(
                text: __('Academic course save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: $this->editingAcademicCourseId
                ? __('Academic course updated successfully.')
                : ($this->isDuplicatingAcademicCourse ? __('Academic course duplicated successfully.') : __('Academic course created successfully.')),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('academic-course-saved');
        $this->closeAcademicCourseModal();
    }

    #[On('confirm-delete-academic-course')]
    public function confirmDeleteAcademicCourse(int $academicCourseId): void
    {
        $this->ensureCan('management.academic-course.delete');
        $this->deletingAcademicCourseId = $academicCourseId;
        $this->showDeleteModal = true;
    }

    public function deleteAcademicCourse(): void
    {
        $this->ensureCan('management.academic-course.delete');

        $academicCourse = $this->academicCourseRepository()->find($this->deletingAcademicCourseId);

        try {
            $this->academicCourseRepository()->delete($academicCourse);
        } catch (Throwable $exception) {
            $this->addError('deleteAcademicCourse', __('Academic course delete failed.'));

            Flux::toast(
                text: __('Academic course delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: __('Academic course deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('academic-course-deleted');
        $this->closeDeleteModal();
    }

    /**
     * @return Collection<int, AcademicYear>
     */
    public function academicYears(): Collection
    {
        return AcademicYear::query()
            ->orderByDesc('name')
            ->get();
    }

    /**
     * @return Collection<int, Program>
     */
    public function programs(): Collection
    {
        return Program::query()
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    public function closeAcademicCourseModal(): void
    {
        $this->showAcademicCourseModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingAcademicCourseId = null;
        $this->resetErrorBag('deleteAcademicCourse');
    }

    public function hasAcademicCourseChanges(): bool
    {
        return $this->currentAcademicCourseState() !== $this->originalAcademicCourseState;
    }

    public function shouldShowSaveAcademicCourseButton(): bool
    {
        if ($this->editingAcademicCourseId === null) {
            return true;
        }

        return $this->hasAcademicCourseChanges();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return AcademicCourseRules::rules($this->editingAcademicCourseId, $this->academic_year_id !== '' ? (int) $this->academic_year_id : null);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return AcademicCourseRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingAcademicCourseId',
            'academic_year_id',
            'program_id',
            'ordering',
            'course_name',
            'sector_name',
            'catechism_avg_score',
            'catechism_training_score',
            'activity_score',
            'is_active',
        ]);

        $this->isDuplicatingAcademicCourse = false;
        $this->catechism_avg_score = '5.00';
        $this->catechism_training_score = '5.00';
        $this->activity_score = 150;
        $this->is_active = true;
        $this->syncOriginalAcademicCourseState();
        $this->resetErrorBag();
    }

    protected function syncOriginalAcademicCourseState(): void
    {
        $this->originalAcademicCourseState = $this->currentAcademicCourseState();
    }

    /**
     * @return array<string, int|string|bool>
     */
    protected function currentAcademicCourseState(): array
    {
        return [
            'academic_year_id' => $this->academic_year_id,
            'program_id' => $this->program_id,
            'ordering' => $this->ordering,
            'course_name' => $this->course_name,
            'sector_name' => $this->sector_name,
            'catechism_avg_score' => $this->catechism_avg_score,
            'catechism_training_score' => $this->catechism_training_score,
            'activity_score' => $this->activity_score,
            'is_active' => $this->is_active,
        ];
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }

    protected function fillAcademicYearDefaults(int $academicYearId): void
    {
        /** @var AcademicYear|null $academicYear */
        $academicYear = AcademicYear::query()->find($academicYearId);

        if ($academicYear === null) {
            return;
        }

        $this->catechism_avg_score = (string) $academicYear->catechism_avg_score;
        $this->catechism_training_score = (string) $academicYear->catechism_training_score;
        $this->activity_score = (int) $academicYear->activity_score;
    }

    protected function duplicateValueForAcademicYear(string $value, string $column, int $academicYearId): string
    {
        $baseValue = trim($value);
        $candidate = $baseValue.' ('.__('Copy').')';
        $sequence = 2;

        while (AcademicCourse::query()
            ->where('academic_year_id', $academicYearId)
            ->where($column, $candidate)
            ->exists()) {
            $candidate = $baseValue.' ('.__('Copy').' '.$sequence.')';
            $sequence++;
        }

        return $candidate;
    }

    protected function academicCourseRepository(): AcademicCourseRepositoryInterface
    {
        return app(AcademicCourseRepositoryInterface::class);
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-courses.academic-course-actions', [
            'academicYears' => $this->academicYears(),
            'programs' => $this->programs(),
        ]);
    }
}
