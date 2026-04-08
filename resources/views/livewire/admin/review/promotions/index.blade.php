<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Promotions')"
        :sub-title="__('Review yearly results, flag children who have not met all conditions, and prepare final promotion decisions.')"
        icon="arrow-trending-up"
    />

    <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('Pending promotion reviews') }}</flux:heading>
        <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('This table keeps both pending reviews and confirmed history for the selected academic year.') }}
        </flux:text>
        <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Pending items appear by default. Switch the review scope filter to see confirmed decisions.') }}
        </flux:text>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <livewire:admin.review.promotions.promotion-list />
    </div>

    <livewire:admin.review.promotions.promotion-actions />
</section>
