<?php

namespace App\Livewire\Admin\Management\AcademicYear;

use App\Repositories\Contracts\AcademicYearRepositoryInterface;
use App\Validation\Admin\Management\AcademicYearRules;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

class AcademicYearActions extends Component
{
    public bool $showAcademicYearModal = false;

    public bool $showSyncAcademicCoursesConfirmModal = false;

    public bool $showAcademicYearDetails = false;

    public bool $showDeleteModal = false;

    public ?int $editingAcademicYearId = null;

    public ?int $deletingAcademicYearId = null;

    public string $name = '';

    #[Validate]
    public int|string $start_year = '';

    #[Validate]
    public int|string $end_year = '';

    #[Validate]
    public string $catechism_start_date = '';

    #[Validate]
    public string $catechism_end_date = '';

    #[Validate]
    public string $catechism_avg_score = '5.00';

    #[Validate]
    public string $catechism_training_score = '5.00';

    #[Validate]
    public string $activity_start_date = '';

    #[Validate]
    public string $activity_end_date = '';

    #[Validate]
    public int $activity_score = 150;

    #[Validate]
    public string $status_academic = 'upcoming';

    public bool $should_create_academic_courses = false;

    public bool $hasConfirmedAcademicCourseSync = false;

    #[Locked]
    public array $originalAcademicYearState = [];

    #[On('open-create-academic-year-modal')]
    public function openCreateModal(): void
    {
        $this->ensureCan('management.academic-year.create');
        $this->resetForm();
        $this->showAcademicYearDetails = false;
        $this->showAcademicYearModal = true;
    }

    #[On('edit-academic-year')]
    public function openEditModal(int $academicYearId): void
    {
        $this->ensureCan('management.academic-year.update');

        $academicYear = $this->academicYearRepository()->find($academicYearId);

        $this->editingAcademicYearId = (int) $academicYear->id;
        $this->start_year = $academicYear->catechism_start_date?->year
            ?? $academicYear->activity_start_date?->year
            ?? $this->extractYearFromAcademicYearName($academicYear->name, 0);
        $this->end_year = $academicYear->catechism_end_date?->year
            ?? $academicYear->activity_end_date?->year
            ?? $this->extractYearFromAcademicYearName($academicYear->name, 1);
        $this->catechism_start_date = $academicYear->formatted_catechism_start_date;
        $this->catechism_end_date = $academicYear->formatted_catechism_end_date;
        $this->catechism_avg_score = (string) $academicYear->catechism_avg_score;
        $this->catechism_training_score = (string) $academicYear->catechism_training_score;
        $this->activity_start_date = $academicYear->formatted_activity_start_date;
        $this->activity_end_date = $academicYear->formatted_activity_end_date;
        $this->activity_score = (int) $academicYear->activity_score;
        $this->status_academic = $academicYear->status_academic;
        $this->should_create_academic_courses = false;
        $this->hasConfirmedAcademicCourseSync = false;
        $this->syncGeneratedAcademicYearName();
        $this->syncOriginalAcademicYearState();
        $this->showAcademicYearDetails = true;
        $this->showAcademicYearModal = true;
    }

    public function continueToAcademicYearDetails(): void
    {
        $this->ensureCan($this->editingAcademicYearId ? 'management.academic-year.update' : 'management.academic-year.create');

        $validated = $this->validate(
            collect(AcademicYearRules::rules())->only(['start_year', 'end_year'])->all(),
            AcademicYearRules::messages(),
        );

        $this->start_year = (int) $validated['start_year'];
        $this->end_year = (int) $validated['end_year'];
        $this->syncGeneratedAcademicYearName();

        $exists = $this->academicYearRepository()
            ->query()
            ->where('name', $this->name)
            ->when(
                $this->editingAcademicYearId,
                fn ($query) => $query->whereKeyNot($this->editingAcademicYearId),
            )
            ->exists();

        if ($exists) {
            $this->addError('start_year', __('This academic year already exists.'));

            return;
        }

        $this->resetErrorBag('start_year');
        $this->resetErrorBag('end_year');
        $this->showAcademicYearDetails = true;
    }

    public function backToAcademicYearSelection(): void
    {
        if ($this->editingAcademicYearId !== null) {
            return;
        }

        $this->showAcademicYearDetails = false;
        $this->resetErrorBag();
    }

    public function saveAcademicYear(): void
    {
        $this->ensureCan($this->editingAcademicYearId ? 'management.academic-year.update' : 'management.academic-year.create');

        $this->syncGeneratedAcademicYearName();

        $validated = $this->validate();

        if ($this->shouldConfirmAcademicCourseSync()) {
            $this->showSyncAcademicCoursesConfirmModal = true;

            return;
        }

        try {
            $this->academicYearRepository()->save(
                $validated,
                $this->editingAcademicYearId,
                $this->should_create_academic_courses,
            );
        } catch (Throwable $exception) {
            $this->addError('start_year', __('Academic year save failed.'));

            Flux::toast(
                text: __('Academic year save failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: $this->editingAcademicYearId ? __('Academic year updated successfully.') : __('Academic year created successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('academic-year-saved');
        $this->closeAcademicYearModal();
    }

    public function confirmSyncAcademicCoursesAndSave(): void
    {
        $this->hasConfirmedAcademicCourseSync = true;
        $this->showSyncAcademicCoursesConfirmModal = false;

        $this->saveAcademicYear();
    }

    #[On('confirm-delete-academic-year')]
    public function confirmDeleteAcademicYear(int $academicYearId): void
    {
        $this->ensureCan('management.academic-year.delete');
        $this->deletingAcademicYearId = $academicYearId;
        $this->showDeleteModal = true;
    }

    public function deleteAcademicYear(): void
    {
        $this->ensureCan('management.academic-year.delete');

        $academicYear = $this->academicYearRepository()->find($this->deletingAcademicYearId);

        try {
            $this->academicYearRepository()->delete($academicYear);
        } catch (Throwable $exception) {
            $this->addError('deleteAcademicYear', __('Academic year delete failed.'));

            Flux::toast(
                text: __('Academic year delete failed.'),
                heading: __('Error'),
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            text: __('Academic year deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('academic-year-deleted');
        $this->closeDeleteModal();
    }

    public function closeAcademicYearModal(): void
    {
        $this->showAcademicYearModal = false;
        $this->showSyncAcademicCoursesConfirmModal = false;
        $this->resetForm();
    }

    public function closeSyncAcademicCoursesConfirmModal(): void
    {
        $this->showSyncAcademicCoursesConfirmModal = false;
        $this->hasConfirmedAcademicCourseSync = false;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingAcademicYearId = null;
        $this->resetErrorBag('deleteAcademicYear');
    }

    public function hasAcademicYearChanges(): bool
    {
        return $this->currentAcademicYearState() !== $this->originalAcademicYearState;
    }

    public function shouldShowSaveAcademicYearButton(): bool
    {
        if (! $this->showAcademicYearDetails) {
            return false;
        }

        if ($this->editingAcademicYearId === null) {
            return true;
        }

        return $this->hasAcademicYearChanges();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return AcademicYearRules::rules($this->editingAcademicYearId);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return AcademicYearRules::messages();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingAcademicYearId',
            'start_year',
            'end_year',
            'catechism_start_date',
            'catechism_end_date',
            'catechism_avg_score',
            'catechism_training_score',
            'activity_start_date',
            'activity_end_date',
            'activity_score',
            'status_academic',
            'should_create_academic_courses',
            'hasConfirmedAcademicCourseSync',
        ]);

        $this->start_year = now()->year;
        $this->end_year = now()->year + 1;
        $this->catechism_avg_score = '5.00';
        $this->catechism_training_score = '5.00';
        $this->activity_score = 150;
        $this->status_academic = 'upcoming';
        $this->should_create_academic_courses = false;
        $this->hasConfirmedAcademicCourseSync = false;
        $this->syncGeneratedAcademicYearName();
        $this->applyDefaultScheduleDates();
        $this->syncOriginalAcademicYearState();
        $this->showAcademicYearDetails = false;
        $this->showSyncAcademicCoursesConfirmModal = false;
        $this->resetErrorBag();
    }

    protected function syncOriginalAcademicYearState(): void
    {
        $this->originalAcademicYearState = $this->currentAcademicYearState();
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentAcademicYearState(): array
    {
        /** @var array<string, mixed> $state */
        $state = $this->all();

        return [
            'start_year' => (int) ($state['start_year'] ?? 0),
            'end_year' => (int) ($state['end_year'] ?? 0),
            'name' => $this->generatedAcademicYearName(),
            'catechism_start_date' => (string) ($state['catechism_start_date'] ?? ''),
            'catechism_end_date' => (string) ($state['catechism_end_date'] ?? ''),
            'catechism_avg_score' => (string) ($state['catechism_avg_score'] ?? '5.00'),
            'catechism_training_score' => (string) ($state['catechism_training_score'] ?? '5.00'),
            'activity_start_date' => (string) ($state['activity_start_date'] ?? ''),
            'activity_end_date' => (string) ($state['activity_end_date'] ?? ''),
            'activity_score' => (int) ($state['activity_score'] ?? 150),
            'status_academic' => (string) ($state['status_academic'] ?? 'upcoming'),
            'should_create_academic_courses' => (bool) ($state['should_create_academic_courses'] ?? false),
        ];
    }

    public function generatedAcademicYearName(): string
    {
        if ($this->start_year === '' || $this->end_year === '') {
            return '';
        }

        return sprintf(
            'NK%s-%s',
            substr((string) $this->start_year, -2),
            substr((string) $this->end_year, -2),
        );
    }

    public function updatedStartYear(): void
    {
        $this->syncGeneratedAcademicYearName();
        $this->applyDefaultScheduleDates();
    }

    public function updatedEndYear(): void
    {
        $this->syncGeneratedAcademicYearName();
        $this->applyDefaultScheduleDates();
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }

    protected function academicYearRepository(): AcademicYearRepositoryInterface
    {
        return app(AcademicYearRepositoryInterface::class);
    }

    protected function shouldConfirmAcademicCourseSync(): bool
    {
        if (! $this->should_create_academic_courses || $this->editingAcademicYearId === null || $this->hasConfirmedAcademicCourseSync) {
            return false;
        }

        return $this->academicYearRepository()
            ->find($this->editingAcademicYearId)
            ->academicCourses()
            ->exists();
    }

    /**
     * @return array<string, string>
     */
    public function statusOptions(): array
    {
        return [
            'upcoming' => __('Upcoming'),
            'ongoing' => __('Ongoing'),
            'finished' => __('Finished'),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function yearOptions(): array
    {
        return range(now()->year, now()->year + 6);
    }

    protected function extractYearFromAcademicYearName(string $name, int $offset): int
    {
        if (preg_match('/NK(\d{2})-(\d{2})/', $name, $matches) === 1) {
            return (int) ('20'.$matches[$offset + 1]);
        }

        return now()->year + $offset;
    }

    protected function syncGeneratedAcademicYearName(): void
    {
        $this->name = $this->generatedAcademicYearName();
    }

    protected function applyDefaultScheduleDates(): void
    {
        if ($this->start_year === '' || $this->end_year === '') {
            return;
        }

        $startDate = CarbonImmutable::create((int) $this->start_year, 9, 1)->format('Y-m-d');
        $endDate = CarbonImmutable::create((int) $this->end_year, 7, 31)->format('Y-m-d');

        $this->catechism_start_date = $startDate;
        $this->catechism_end_date = $endDate;
        $this->activity_start_date = $startDate;
        $this->activity_end_date = $endDate;
    }

    public function render(): View
    {
        return view('livewire.admin.management.academic-year.academic-year-actions');
    }
}
