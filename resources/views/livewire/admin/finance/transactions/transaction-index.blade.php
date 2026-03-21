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
        :types="[
            ['value' => 'income', 'label' => __('Income')],
            ['value' => 'expense', 'label' => __('Expense')],
        ]"
        :statuses="[
            ['value' => 'pending', 'label' => __('Pending')],
            ['value' => 'completed', 'label' => __('Completed')],
        ]"
    />

    <livewire:admin.finance.transactions.transaction-list
        :search="$search"
        :per-page="$perPage"
        :selected-type="$selectedType"
        :selected-status="$selectedStatus"
        :key="'transaction-list-'.md5($search.'|'.$perPage.'|'.$selectedType.'|'.$selectedStatus)"
        lazy
    />

    <livewire:admin.finance.transactions.transaction-actions />
</section>
