<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Catechism - sector classes')"
        :sub-title="__('Manage catechism classes and activity sectors for each academic year.')"
        icon="rectangle-stack"
        :button-label="__('Add catechism - sector class')"
        button-action="openCreateModal"
        permission="management.academic-course.create"
    />

    <livewire:admin.management.academic-courses.academic-course-list lazy />

    <livewire:admin.management.academic-courses.academic-course-actions />
</section>
