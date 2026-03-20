<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Roles')"
        :sub-title="__('Create roles and assign permissions in one place.')"
        icon="shield-check"
        :button-label="__('Add role')"
        button-action="openCreateModal"
        permission="access.role.create"
    />

    <x-app-filter :has-pages="true" />

    <livewire:admin.access.roles.role-list :search="$search" :per-page="$perPage" :key="'role-list-'.md5($search.'|'.$perPage)" lazy />

    <livewire:admin.access.roles.role-actions />
</section>
