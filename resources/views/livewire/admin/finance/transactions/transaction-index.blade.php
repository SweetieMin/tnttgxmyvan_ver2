<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Common fund')"
        :sub-title="__('Manage income, expenses, and supporting documents for the common fund.')"
        icon="banknotes"
        :button-label="__('Add transaction')"
        button-action="openCreateModal"
        permission="finance.transaction.create"
    />

    <x-app-filter
        :has-pages="true"
        :export-data="true"
        :can-export-data="'finance.transaction.view'"
        :types="[
            ['value' => 'income', 'label' => __('Income')],
            ['value' => 'expense', 'label' => __('Expense')],
        ]"
        :categories="$categories"
        :statuses="[
            ['value' => 'pending', 'label' => __('Pending')],
            ['value' => 'completed', 'label' => __('Completed')],
        ]"
    />

    <livewire:admin.finance.transactions.transaction-list
        :search="$search"
        :per-page="$perPage"
        :selected-type="$selectedType"
        :selected-category="$selectedCategory"
        :selected-status="$selectedStatus"
        :key="'transaction-list-'.md5($search.'|'.$perPage.'|'.$selectedType.'|'.$selectedCategory.'|'.$selectedStatus)"
        lazy
    />

    <livewire:admin.finance.transactions.transaction-actions />
    <livewire:admin.finance.transactions.transaction-export />
</section>
