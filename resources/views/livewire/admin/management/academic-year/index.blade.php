<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Academic years')"
        :sub-title="__('Manage academic years, catechism schedules, and children activities.')"
        icon="calendar-days"
        :button-label="__('Add academic year')"
        button-action="openCreateModal"
        permission="management.academic-year.create"
    />

    <x-app-filter :has-pages="true" />

    <livewire:admin.management.academic-year.academic-year-list :search="$search" :per-page="$perPage" :key="'academic-year-list-'.md5($search.'|'.$perPage)" lazy />

    <livewire:admin.management.academic-year.academic-year-actions />
</section>
