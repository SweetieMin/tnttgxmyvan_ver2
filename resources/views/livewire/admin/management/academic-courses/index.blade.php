<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Catechism - sector classes')"
        :sub-title="__('Manage the catechism class and sector structure for each academic year. Staffing assignments are handled in sector assignments.')"
        icon="rectangle-stack"
        :button-label="__('Add catechism - sector class')"
        button-action="openCreateModal"
        permission="management.academic-course.create"
    />

    <livewire:admin.management.academic-courses.academic-course-list lazy />

    <livewire:admin.management.academic-courses.academic-course-actions />
</section>
