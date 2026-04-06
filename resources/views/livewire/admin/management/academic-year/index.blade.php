<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Academic years')"
        :sub-title="__('Manage academic years, catechism schedules, and children activities.')"
        icon="calendar-days"
        :button-label="__('Add academic year')"
        button-action="openCreateModal"
        permission="management.academic-year.create"
    />

    <livewire:admin.management.academic-year.academic-year-list />

    <livewire:admin.management.academic-year.academic-year-actions />
</section>
