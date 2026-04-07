<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Gradebooks')"
        :sub-title="__('Manage semester catechism scores, conduct scores, and semester confirmations.')"
        icon="book-open"
    />

    <x-management-module-placeholder
        :heading="__('Gradebooks')"
        :description="__('This module will let catechists enter semester grades and lock each semester after confirmation.')"
        :highlights="[
            __('Record four monthly scores and one exam score per semester.'),
            __('Confirm and lock each semester after review by head catechists.'),
            __('Calculate yearly catechism and conduct results automatically.'),
        ]"
    />
</section>
