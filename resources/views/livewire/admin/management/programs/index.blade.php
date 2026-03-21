<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Programs')"
        :sub-title="__('Manage study programs, sectors, and display order.')"
        icon="academic-cap"
        :button-label="__('Add program')"
        button-action="openCreateModal"
        permission="management.program.create"
    />

    <x-app-filter :has-pages="true" />

    <livewire:admin.management.programs.program-list :search="$search" :per-page="$perPage" :key="'program-list-'.md5($search.'|'.$perPage)" lazy />

    <livewire:admin.management.programs.program-actions />
</section>
