<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Common fund')"
        :sub-title="__('Manage income, expenses, and supporting documents for the common fund.')"
        icon="banknotes"
        :button-label="__('Add transaction')"
        button-action="openCreateModal"
        permission="finance.transaction.create"
    />

    <livewire:admin.finance.transactions.transaction-list lazy />

    <livewire:admin.finance.transactions.transaction-actions />
</section>
