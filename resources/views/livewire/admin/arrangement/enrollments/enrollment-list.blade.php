<div class="flex flex-col gap-4">
    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-3">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    :label="__('Search children')"
                    :placeholder="__('Search by name, Christian name, or username...')"
                    icon="magnifying-glass"
                />
            </div>

            <div class="xl:col-span-2">
                <flux:select
                    wire:model.live="assignmentFilter"
                    variant="listbox"
                    :label="__('Assignment status')"
                >
                    <flux:select.option value="all">{{ __('All children') }}</flux:select.option>
                    <flux:select.option value="assigned">{{ __('Assigned only') }}</flux:select.option>
                    <flux:select.option value="unassigned">{{ __('Unassigned only') }}</flux:select.option>
                    <flux:select.option value="graduates">{{ __('Graduation candidates') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="xl:col-span-2">
                <flux:select
                    wire:model.live="classFilterAcademicCourseId"
                    variant="listbox"
                    :label="__('Class filter')"
                    :placeholder="__('All classes')"
                    :disabled="$academicCourses->isEmpty()"
                >
                    <flux:select.option value="">{{ __('All classes') }}</flux:select.option>
                    @foreach ($courseOptions as $courseId => $label)
                        <flux:select.option value="{{ $courseId }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="xl:col-span-2">
                <flux:select
                    wire:model.live="previousResultFilter"
                    variant="listbox"
                    :label="__('Previous result')"
                >
                    <flux:select.option value="passed">{{ __('Passed') }}</flux:select.option>
                    <flux:select.option value="pending_review">{{ __('Pending review') }}</flux:select.option>
                    <flux:select.option value="all">{{ __('All results') }}</flux:select.option>
                </flux:select>
            </div>

            <div class="xl:col-span-3">
                <div class="flex h-full items-end justify-end">
                    @if ($hasPendingChanges)
                        <flux:badge color="amber">{{ __('Unsaved changes') }}</flux:badge>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-end gap-3">
            @if ($previousAcademicYear)
                <flux:button
                    variant="ghost"
                    icon="arrow-trending-up"
                    wire:click="applyPromotionSuggestions"
                    :disabled="$academicCourses->isEmpty() || ! $this->canUpdateAssignments()"
                >
                    {{ __('Apply promotion suggestions') }}
                </flux:button>
            @endif

            <flux:button variant="ghost" wire:click="selectVisibleChildren">
                {{ __('Select all visible') }}
            </flux:button>

            <flux:button variant="ghost" wire:click="clearSelection">
                {{ __('Clear selection') }}
            </flux:button>

            <div class="min-w-72 flex-1">
                <flux:select
                    wire:model.live="bulkAcademicCourseId"
                    variant="listbox"
                    :label="__('Bulk class')"
                    :placeholder="__('Select...')"
                    :disabled="$academicCourses->isEmpty()"
                >
                    @foreach ($courseOptions as $courseId => $label)
                        <flux:select.option value="{{ $courseId }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:button
                variant="ghost"
                icon="arrow-down-on-square-stack"
                wire:click="applyBulkCourseSelection"
                :disabled="$academicCourses->isEmpty() || ! $this->canUpdateAssignments()"
            >
                {{ __('Apply to selected') }}
            </flux:button>

            <flux:button
                variant="primary"
                icon="check"
                wire:click="saveAssignments"
                :disabled="! $hasPendingChanges || ! $this->canUpdateAssignments()"
            >
                {{ __('Save assignments') }}
            </flux:button>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <flux:badge color="zinc">{{ count($selectedUserIds) }}</flux:badge>
            <span>{{ __('children selected') }}</span>
        </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        {{ $this->table }}
    </div>
</div>
