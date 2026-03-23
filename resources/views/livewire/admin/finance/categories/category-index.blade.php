<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Categories')"
        :sub-title="__('Manage finance categories, display order, and active status.')"
        icon="tag"
        :button-label="__('Add category')"
        button-action="openCreateModal"
        permission="finance.category.create"
    />

    <x-app-filter :has-pages="true" />

    <livewire:admin.finance.categories.category-list :search="$search" :per-page="$perPage" :key="'category-list-'.md5($search.'|'.$perPage)" lazy />

    <livewire:admin.finance.categories.category-actions />
</section>
