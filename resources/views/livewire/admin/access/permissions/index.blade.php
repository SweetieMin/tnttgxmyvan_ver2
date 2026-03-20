<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Permissions')"
        :sub-title="__('Manage permission keys and review where they are used.')"
        icon="key"
        :button-label="__('Add permission')"
        button-action="openCreateModal"
        permission="access.permission.create"
    />

    <x-app-filter :has-pages="true" />

    <livewire:admin.access.permissions.permission-list :search="$search" :per-page="$perPage" :key="'permission-list-'.md5($search.'|'.$perPage)" lazy />

    <livewire:admin.access.permissions.permission-actions />
</section>
