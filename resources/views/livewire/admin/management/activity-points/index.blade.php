<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Activity points')"
        :sub-title="__('Review the activity point ledger generated from attendance approvals, regulations, and manual adjustments.')"
        icon="sparkles"
    />

    <x-management-module-placeholder
        :heading="__('Activity points')"
        :description="__('This module will show the activity point ledger for each enrollment instead of mixing it with system logs.')"
        :highlights="[
            __('Store approved attendance points as enrollment activity entries.'),
            __('Track regulation-based additions and deductions.'),
            __('Summarize each child’s total activity score for promotion decisions.'),
        ]"
    />
</section>
