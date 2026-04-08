<section class="flex flex-col gap-4">
    <x-app-heading
        :title="__('Enrollments')"
        :sub-title="__('Assign children to classes quickly for each academic year, then review promotion-ready placements before the new year starts.')"
        icon="clipboard-document-check"
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
                    <flux:text class="font-medium text-zinc-950 dark:text-zinc-50">{{ __('Enrollment workspace') }}</flux:text>
                    <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Use this screen to prepare class placement for the new year, carry forward promotion-ready children, and adjust individual assignments before grades or attendance begin.') }}
                    </flux:text>
                </div>
            </div>
        </div>
    </div>

    @if ($academicYearId)
        <livewire:admin.arrangement.enrollments.enrollment-actions
            :academic-year-id="(int) $academicYearId"
            :key="'enrollment-actions-'.$academicYearId"
        />

        <livewire:admin.arrangement.enrollments.enrollment-list
            :academic-year-id="(int) $academicYearId"
            :key="'enrollment-list-'.$academicYearId"
        />
    @else
        <flux:callout icon="information-circle" :heading="__('No academic year available.')">
            {{ __('Create an academic year before assigning children to classes.') }}
        </flux:callout>
    @endif
</section>
