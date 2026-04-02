<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Attendance schedules')"
        :sub-title="__('Create attendance sessions with a required date, scanning window, and activity points.')"
        icon="calendar-days"
    />

    <x-management-module-placeholder
        :heading="__('Attendance schedules')"
        :description="__('This module will define when children can check in and how many activity points each session is worth.')"
        :highlights="[
            __('Set the date, start time, end time, and point value for each attendance session.'),
            __('Limit QR scanning to the configured attendance window.'),
            __('Use schedules as the source for attendance approvals and point awards.'),
        ]"
    />
</section>
