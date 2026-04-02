<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Enrollments')"
        :sub-title="__('Assign children to classes for each academic year and track their study status.')"
        icon="clipboard-document-check"
    />

    <x-management-module-placeholder
        :heading="__('Enrollments')"
        :description="__('Use this module to place children into catechism-sector classes for each academic year.')"
        :highlights="[
            __('Assign children to academic courses by year.'),
            __('Track study status and class placement history.'),
            __('Prepare enrollment data for grades, attendance, and promotions.'),
        ]"
    />
</section>
