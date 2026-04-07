<?php

namespace App\Livewire\Admin\Management\AttendanceSchedules;

use App\Models\AcademicYear;
use App\Models\AttendanceSchedule;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Omnia\LivewireCalendar\LivewireCalendar;

class AttendanceScheduleCalendar extends LivewireCalendar
{
    public $dayClickEnabled;

    /**
     * @param  array<string, mixed>  $extras
     */
    public function afterMount($extras = []): void
    {
        $this->beforeCalendarView = 'livewire.admin.management.attendance-schedules.calendar-before';
        $this->afterCalendarView = 'livewire.admin.management.attendance-schedules.calendar-after';
    }

    #[On('attendance-schedule-saved')]
    #[On('attendance-schedule-deleted')]
    public function loadAttendanceSchedule() {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function events(): Collection
    {
        return AttendanceSchedule::query()
            ->with('regulation')
            ->whereBetween('attendance_date', [
                $this->gridStartsAt->toDateString(),
                $this->gridEndsAt->toDateString(),
            ])
            ->orderBy('attendance_date')
            ->orderBy('start_time')
            ->get()
            ->map(function (AttendanceSchedule $attendanceSchedule): array {
                return [
                    'id' => $attendanceSchedule->id,
                    'title' => $this->calendarEventTitle($attendanceSchedule),
                    'mobile_label' => $this->calendarEventMobileLabel($attendanceSchedule),
                    'description' => $this->calendarEventDescription($attendanceSchedule),
                    'date' => $attendanceSchedule->attendance_date,
                    ...$this->calendarEventColorClasses($attendanceSchedule),
                ];
            });
    }

    public function onDayClick($year, $month, $day): void
    {
        $this->dispatch(
            'open-create-attendance-schedule-modal',
            attendanceDate: now()->setDate((int) $year, (int) $month, (int) $day)->toDateString(),
            academicYearId: $this->defaultAcademicYearId(),
        );
    }

    public function onEventClick($eventId): void
    {
        $this->dispatch('edit-attendance-schedule', attendanceScheduleId: (int) $eventId);
    }

    public function onEventDropped($eventId, $year, $month, $day): void
    {
        /** @var AttendanceSchedule $attendanceSchedule */
        $attendanceSchedule = AttendanceSchedule::query()->findOrFail($eventId);
        $attendanceSchedule->update([
            'attendance_date' => now()->setDate((int) $year, (int) $month, (int) $day)->toDateString(),
        ]);

        Flux::toast(
            text: __('Attendance schedule moved successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
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

    protected function calendarEventTitle(AttendanceSchedule $attendanceSchedule): string
    {
        return (string) $attendanceSchedule->title;
    }

    protected function calendarEventDescription(AttendanceSchedule $attendanceSchedule): string
    {
        $startTime = substr((string) $attendanceSchedule->start_time, 0, 5);
        $endTime = substr((string) $attendanceSchedule->end_time, 0, 5);

        return collect([
            $startTime.' - '.$endTime,
        ])
            ->filter()
            ->implode(' • ');
    }

    protected function calendarEventMobileLabel(AttendanceSchedule $attendanceSchedule): string
    {
        $label = str($attendanceSchedule->regulation?->short_desc ?? $attendanceSchedule->regulation?->description ?? '')
            ->lower()
            ->value();

        if (str_contains($label, 'thánh lễ')) {
            return __('Mass');
        }

        if (str_contains($label, 'chầu')) {
            return __('Adoration');
        }

        return (string) $attendanceSchedule->title;
    }

    /**
     * @return array{
     *     dot_class: string,
     *     border_class: string,
     *     background_class: string,
     *     hover_class: string
     * }
     */
    protected function calendarEventColorClasses(AttendanceSchedule $attendanceSchedule): array
    {
        $label = str($attendanceSchedule->regulation?->short_desc ?? $attendanceSchedule->regulation?->description ?? '')
            ->lower()
            ->value();

        if (str_contains($label, 'thánh lễ')) {
            return [
                'dot_class' => 'bg-red-500',
                'border_class' => 'border-red-200/80 dark:border-red-500/35',
                'background_class' => 'bg-red-50/90 dark:bg-zinc-900/95',
                'hover_class' => 'hover:border-red-300 hover:bg-red-50 dark:hover:border-red-400/55 dark:hover:bg-zinc-800/95',
            ];
        }

        if (str_contains($label, 'chầu')) {
            return [
                'dot_class' => 'bg-amber-500',
                'border_class' => 'border-amber-200/80 dark:border-amber-500/35',
                'background_class' => 'bg-amber-50/90 dark:bg-zinc-900/95',
                'hover_class' => 'hover:border-amber-300 hover:bg-amber-50 dark:hover:border-amber-400/55 dark:hover:bg-zinc-800/95',
            ];
        }

        return [
            'dot_class' => 'bg-(--color-accent)',
            'border_class' => 'border-zinc-200/70 dark:border-zinc-700/70',
            'background_class' => 'bg-white/95 dark:bg-zinc-900/95',
            'hover_class' => 'hover:border-(--color-accent) hover:shadow-sm hover:shadow-sky-100/40 dark:hover:bg-zinc-800/95',
        ];
    }
}
