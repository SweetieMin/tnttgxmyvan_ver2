<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Programs')"
        :sub-title="__('Manage study programs, sectors, and display order.')"
        icon="academic-cap"
        :button-label="__('Add program')"
        button-action="openCreateModal"
        permission="management.program.create"
    />

    <livewire:admin.management.programs.program-list lazy />

    <livewire:admin.management.programs.program-actions />
</section>
