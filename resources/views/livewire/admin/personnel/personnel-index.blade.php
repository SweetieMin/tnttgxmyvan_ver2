<section class="flex flex-col gap-4">
    <x-app-heading
        :title="$this->title()"
        :sub-title="$this->subtitle()"
        :icon="$this->icon()"
        :button-label="$this->createButtonLabel()"
        button-action="openCreateModal"
        :permission="$this->createPermission()"
    />

    <x-app-filter :has-pages="true" :roles="$this->roleOptions()" :statuses="$this->statusOptions()" />

    <livewire:admin.personnel.personnel-list
        :group="$group"
        :search="$search"
        :per-page="$perPage"
        :selected-role="$selectedRole"
        :selected-status="$selectedStatus"
        :key="'personnel-list-'.md5($group.'|'.$search.'|'.$perPage.'|'.$selectedRole.'|'.$selectedStatus)"
        lazy
    />
</section>
