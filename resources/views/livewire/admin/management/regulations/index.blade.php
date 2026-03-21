<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Regulations')"
        :sub-title="__('Manage regulations, points, and display order.')"
        icon="clipboard-document-list"
        :button-label="__('Add regulation')"
        button-action="openCreateModal"
        permission="management.regulation.create"
    />

    <x-app-filter :has-pages="true" />

    <livewire:admin.management.regulations.regulation-list :search="$search" :per-page="$perPage" :key="'regulation-list-'.md5($search.'|'.$perPage)" lazy />

    <livewire:admin.management.regulations.regulation-actions />
</section>
