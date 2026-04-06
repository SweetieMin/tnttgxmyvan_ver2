<?php

namespace App\Livewire\Admin\Management\AttendanceSchedules;

use App\Models\AcademicYear;
use App\Models\AttendanceSchedule;
use App\Models\Regulation;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AttendanceScheduleActions extends Component
{
    public ?int $editingAttendanceScheduleId = null;

    public ?int $deletingAttendanceScheduleId = null;

    public int|string $academic_year_id = '';

    public string $title = '';

    public string $attendance_date = '';

    public string $start_time = '07:00';

    public string $end_time = '08:30';

    #[Validate]
    public int|string $regulation_id = '';

    public int|string $points = 0;

    public bool $is_active = true;

    #[On('open-create-attendance-schedule-modal')]
    public function openCreateModal(?string $attendanceDate = null, ?int $academicYearId = null): void
    {
        $this->ensureCan('management.attendance-schedule.create');

        $this->resetForm();
        $this->academic_year_id = $academicYearId ?? $this->defaultAcademicYearId();
        $this->attendance_date = $attendanceDate ?? now()->toDateString();
        $this->syncGeneratedTitle();

        Flux::modal('attendance-schedule-form')->show();
    }

    #[On('edit-attendance-schedule')]
    public function openEditModal(int $attendanceScheduleId): void
    {
        $this->ensureCan('management.attendance-schedule.update');

        /** @var AttendanceSchedule $attendanceSchedule */
        $attendanceSchedule = AttendanceSchedule::query()->findOrFail($attendanceScheduleId);

        $this->editingAttendanceScheduleId = (int) $attendanceSchedule->id;
        $this->academic_year_id = (int) $attendanceSchedule->academic_year_id;
        $this->attendance_date = $attendanceSchedule->attendance_date?->toDateString() ?? '';
        $this->start_time = substr((string) $attendanceSchedule->start_time, 0, 5);
        $this->end_time = substr((string) $attendanceSchedule->end_time, 0, 5);
        $this->regulation_id = $attendanceSchedule->regulation_id ?? '';
        $this->points = (int) $attendanceSchedule->points;
        $this->is_active = (bool) $attendanceSchedule->is_active;
        $this->syncGeneratedTitle();
        $this->resetErrorBag();

        Flux::modal('attendance-schedule-form')->show();
    }

    public function updatedAttendanceDate(): void
    {
        $this->syncGeneratedTitle();
    }

    public function updatedRegulationId($value): void
    {
        if (blank($value)) {
            $this->points = 0;
            $this->syncGeneratedTitle();

            return;
        }

        /** @var Regulation|null $regulation */
        $regulation = Regulation::query()->find($value);

        if ($regulation === null) {
            $this->syncGeneratedTitle();

            return;
        }

        $this->points = (int) $regulation->points;
        $this->syncGeneratedTitle();
    }

    public function saveAttendanceSchedule(): void
    {
        $this->ensureCan($this->editingAttendanceScheduleId === null ? 'management.attendance-schedule.create' : 'management.attendance-schedule.update');

        $this->syncGeneratedTitle();

        $validated = $this->validate($this->rules(), $this->messages());

        $payload = [
            'academic_year_id' => (int) $validated['academic_year_id'],
            'title' => $this->title,
            'attendance_date' => (string) $validated['attendance_date'],
            'start_time' => (string) $validated['start_time'],
            'end_time' => (string) $validated['end_time'],
            'regulation_id' => filled($validated['regulation_id']) ? (int) $validated['regulation_id'] : null,
            'points' => (int) $validated['points'],
            'is_active' => (bool) $validated['is_active'],
            'created_by' => Auth::id(),
        ];

        if ($this->editingAttendanceScheduleId === null) {
            AttendanceSchedule::query()->create($payload);

            Flux::toast(
                text: __('Attendance schedule created successfully.'),
                heading: __('Success'),
                variant: 'success',
            );
        } else {
            AttendanceSchedule::query()
                ->findOrFail($this->editingAttendanceScheduleId)
                ->update($payload);

            Flux::toast(
                text: __('Attendance schedule updated successfully.'),
                heading: __('Success'),
                variant: 'success',
            );
        }

        $this->dispatch('attendance-schedule-saved');
        $this->closeAttendanceScheduleModal();
    }

    public function confirmDeleteAttendanceSchedule(): void
    {
        $this->ensureCan('management.attendance-schedule.delete');

        if ($this->editingAttendanceScheduleId === null) {
            return;
        }

        $this->deletingAttendanceScheduleId = $this->editingAttendanceScheduleId;

        Flux::modal('delete-attendance-schedule')->show();
    }

    public function deleteAttendanceSchedule(): void
    {
        $this->ensureCan('management.attendance-schedule.delete');

        if ($this->deletingAttendanceScheduleId === null) {
            return;
        }

        AttendanceSchedule::query()->findOrFail($this->deletingAttendanceScheduleId)->delete();

        Flux::toast(
            text: __('Attendance schedule deleted successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->dispatch('attendance-schedule-deleted');
        $this->closeDeleteModal();
        $this->closeAttendanceScheduleModal();
    }

    public function closeAttendanceScheduleModal(): void
    {
        Flux::modal('attendance-schedule-form')->close();
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        Flux::modal('delete-attendance-schedule')->close();
        $this->deletingAttendanceScheduleId = null;
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

    /**
     * @return Collection<int, Regulation>
     */
    public function regulations(): Collection
    {
        return Regulation::query()
            ->where('type', 'plus')
            ->orderBy('ordering')
            ->orderBy('id')
            ->get();
    }

    public function selectedRegulationShortDescription(): string
    {
        if (blank($this->regulation_id)) {
            return '';
        }

        /** @var Regulation|null $regulation */
        $regulation = $this->regulations()->firstWhere('id', (int) $this->regulation_id);

        if ($regulation === null) {
            return '';
        }

        return (string) ($regulation->short_desc ?: $regulation->description);
    }

    public function selectedRegulationDescription(): string
    {
        if (blank($this->regulation_id)) {
            return '';
        }

        /** @var Regulation|null $regulation */
        $regulation = $this->regulations()->firstWhere('id', (int) $this->regulation_id);

        if ($regulation === null) {
            return '';
        }

        return (string) $regulation->description;
    }

    public function render(): View
    {
        return view('livewire.admin.management.attendance-schedules.attendance-schedule-actions');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', Rule::exists(AcademicYear::class, 'id')],
            'attendance_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'regulation_id' => ['required', 'integer', Rule::exists(Regulation::class, 'id')],
            'points' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'academic_year_id.required' => __('Academic year is required.'),
            'academic_year_id.integer' => __('Selected academic year is invalid.'),
            'academic_year_id.exists' => __('Selected academic year is invalid.'),
            'attendance_date.required' => __('Attendance date is required.'),
            'start_time.required' => __('Start time is required.'),
            'end_time.required' => __('End time is required.'),
            'end_time.after' => __('End time must be after the start time.'),
            'regulation_id.required' => __('Regulation is required.'),
            'regulation_id.integer' => __('Selected regulation is invalid.'),
            'regulation_id.exists' => __('Selected regulation is invalid.'),
            'points.required' => __('Points are required.'),
            'points.integer' => __('Points must be an integer.'),
        ];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingAttendanceScheduleId',
            'deletingAttendanceScheduleId',
            'academic_year_id',
            'title',
            'attendance_date',
            'regulation_id',
        ]);

        $this->start_time = '07:00';
        $this->end_time = '08:30';
        $this->points = 0;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    protected function syncGeneratedTitle(): void
    {
        if ($this->attendance_date === '') {
            $this->title = '';

            return;
        }

        $dateLabel = Carbon::parse($this->attendance_date)->format('d/m/y');
        $shortDescription = $this->selectedRegulationShortDescription();

        $this->title = trim(collect([$shortDescription, $dateLabel])->filter()->implode(' '));
    }

    protected function defaultAcademicYearId(): int|string
    {
        /** @var AcademicYear|null $academicYear */
        $academicYear = AcademicYear::query()
            ->where('status_academic', 'ongoing')
            ->latest('id')
            ->first();

        if ($academicYear !== null) {
            return (int) $academicYear->id;
        }

        return AcademicYear::query()->latest('id')->value('id') ?? '';
    }

    protected function ensureCan(string $permission): void
    {
        abort_unless((bool) Auth::user()?->can($permission), 403);
    }
}
