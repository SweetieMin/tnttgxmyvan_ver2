<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Regulations')"
        :sub-title="__('Manage regulations, points, and display order.')"
        icon="clipboard-document-list"
        :button-label="__('Add regulation')"
        button-action="openCreateModal"
        permission="management.regulation.create"
    />

    <livewire:admin.management.regulations.regulation-list lazy />

    <livewire:admin.management.regulations.regulation-actions />
</section>
