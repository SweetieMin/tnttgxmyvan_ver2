<div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1">
            <flux:heading size="lg">{{ __('Class load overview') }}</flux:heading>
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Review how many children are currently placed in each class before saving changes.') }}
            </flux:text>
        </div>
        <flux:badge color="zinc">{{ $courseSummaries->count() }}</flux:badge>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
        @forelse ($courseSummaries as $summary)
            <div
                class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/50"
                wire:key="course-summary-{{ $summary['course']->id }}"
            >
                <flux:text class="font-medium text-zinc-950 dark:text-zinc-50">{{ $summary['course']->course_name }}</flux:text>
                <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $summary['course']->sector_name }}</flux:text>
                <flux:heading size="lg" class="mt-4">{{ $summary['assigned_count'] }}</flux:heading>
                <flux:text class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Assigned children') }}</flux:text>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-200 bg-zinc-50 p-6 text-center dark:border-zinc-800 dark:bg-zinc-950/50 md:col-span-2 xl:col-span-4">
                <flux:text>{{ __('No catechism classes found for this academic year.') }}</flux:text>
            </div>
        @endforelse
    </div>
</div>
