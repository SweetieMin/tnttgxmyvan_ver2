<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Attendance check-ins')"
        :sub-title="__('Scan QR codes, review attendance records, and approve the sessions that should count for activity points.')"
        icon="qr-code"
    />

    <x-management-module-placeholder
        :heading="__('Attendance check-ins')"
        :description="__('This module will record live check-ins and hold them for approval before activity points are awarded.')"
        :highlights="[
            __('Scan by QR code or record a manual make-up check-in.'),
            __('Restrict scanning by role and sector assignment.'),
            __('Approve finished attendance lists before applying points.'),
        ]"
    />
</section>
