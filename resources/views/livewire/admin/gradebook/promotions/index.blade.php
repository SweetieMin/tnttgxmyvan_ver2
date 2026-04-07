<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Promotions')"
        :sub-title="__('Review yearly results, flag children who have not met all conditions, and prepare final promotion decisions.')"
        icon="arrow-trending-up"
    />

    <x-management-module-placeholder
        :heading="__('Promotions')"
        :description="__('This module will calculate yearly eligibility and prepare the review list for chaplains or deacons.')"
        :highlights="[
            __('Check catechism, conduct, and activity conditions together.'),
            __('List children who still need a final review.'),
            __('Prepare promotion outcomes for the next academic year.'),
        ]"
    />
</section>
