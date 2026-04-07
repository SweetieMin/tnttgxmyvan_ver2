<div class="flex flex-col gap-4">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ($overviewStats as $stat)
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900" wire:key="sector-assignment-stat-{{ \Illuminate\Support\Str::slug($stat['label']) }}">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $stat['label'] }}</flux:text>
                <flux:heading size="xl" class="mt-2">{{ $stat['value'] }}</flux:heading>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Sector leader assignments') }}</flux:heading>
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Assign sector heads, vice heads, and leaders for each sector in :academicYear.', ['academicYear' => $academicYear?->name]) }}
                </flux:text>
            </div>
            <flux:badge color="zinc">{{ $sectorAssignments->count() }}</flux:badge>
        </div>

        <div class="mt-4 space-y-3">
            @forelse ($sectorAssignments as $sector)
                <div
                    class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/50"
                    wire:key="sector-assignment-{{ \Illuminate\Support\Str::slug($sector['sector_name']) }}"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-1">
                            <flux:heading size="sm">{{ $sector['sector_name'] }}</flux:heading>
                            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ implode(', ', $sector['course_names']) }}</flux:text>
                        </div>

                        @if ($this->canUpdateAssignments())
                            <flux:button
                                variant="ghost"
                                size="sm"
                                wire:click="editLeaderAssignment(@js($sector['sector_name']))"
                            >
                                {{ __('Assign leaders') }}
                            </flux:button>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                            <flux:text class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Sector head') }}</flux:text>
                            <flux:text class="mt-2 font-medium text-zinc-950 dark:text-zinc-50">
                                {{ $sector['sector_head'] ?? __('Not assigned') }}
                            </flux:text>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                            <flux:text class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Vice sector head') }}</flux:text>
                            <flux:text class="mt-2 font-medium text-zinc-950 dark:text-zinc-50">
                                {{ $sector['vice_sector_head'] ?? __('Not assigned') }}
                            </flux:text>
                        </div>

                        <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                            <flux:text class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Sector leaders') }}</flux:text>
                            <flux:text class="mt-2 font-medium text-zinc-950 dark:text-zinc-50">
                                {{ $sector['leaders'] !== [] ? implode(', ', $sector['leaders']) : __('Not assigned') }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-zinc-200 bg-zinc-50 p-6 text-center dark:border-zinc-800 dark:bg-zinc-950/50">
                    <flux:text>{{ __('No sectors found for this academic year.') }}</flux:text>
                </div>
            @endforelse
        </div>
    </div>
</div>
