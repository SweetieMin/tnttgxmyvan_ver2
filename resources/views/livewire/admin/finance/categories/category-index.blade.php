<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Categories')"
        :sub-title="__('Manage finance categories, display order, and active status.')"
        icon="tag"
        :button-label="__('Add category')"
        button-action="openCreateModal"
        permission="finance.category.create"
    />

    <livewire:admin.finance.categories.category-list lazy />

    <livewire:admin.finance.categories.category-actions />
</section>
