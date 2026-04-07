<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Sector assignments')"
        :sub-title="__('Assign sector heads, vice heads, and leaders for each activity sector in an academic year.')"
        icon="flag"
    />

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <flux:select
                    wire:model.live="academicYearId"
                    variant="listbox"
                    :label="__('Academic year')"
                    :placeholder="__('Select...')"
                >
                    @foreach ($academicYears as $academicYear)
                        <flux:select.option value="{{ $academicYear->id }}">{{ $academicYear->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="lg:col-span-2">
                <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950/50">
                    <flux:text class="font-medium text-zinc-950 dark:text-zinc-50">{{ __('Assignment overview') }}</flux:text>
                    <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Use this screen to assign sector heads, vice heads, and leaders for each activity sector. Class catechist assignments are handled in class assignments.') }}
                    </flux:text>
                </div>
            </div>
        </div>
    </div>

    @if ($academicYearId)
        <livewire:admin.arrangement.sector-assignments.sector-assignment-list
            :academic-year-id="(int) $academicYearId"
            :key="'sector-assignment-list-'.$academicYearId"
        />

        <livewire:admin.arrangement.sector-assignments.sector-assignment-actions
            :academic-year-id="(int) $academicYearId"
            :key="'sector-assignment-actions-'.$academicYearId"
        />
    @else
        <flux:callout icon="information-circle" :heading="__('No academic year available.')">
            {{ __('Create an academic year before assigning sector leaders.') }}
        </flux:callout>
    @endif
</section>
