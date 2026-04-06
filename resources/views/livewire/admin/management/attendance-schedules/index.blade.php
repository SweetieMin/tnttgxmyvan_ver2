<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Attendance schedules')"
        :sub-title="__('Create attendance sessions with a required date, scanning window, and activity points.')"
        icon="calendar-days"
        :button-label="__('Add attendance schedule')"
        button-action="openCreateModal"
        permission="management.attendance-schedule.create"
    />

    <livewire:admin.management.attendance-schedules.attendance-schedule-calendar />
    <livewire:admin.management.attendance-schedules.attendance-schedule-actions />
</section>
