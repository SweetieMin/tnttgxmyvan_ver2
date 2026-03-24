@blaze

@props([
    'types' => [],
    'categories' => [],
    'academicYears' => [],
    'statuses' => [],
    'dateRange' => false,
    'hasPages' => false,
    'showSearch' => true,
    'showResetControl' => true,
    'resetAction' => 'resetFilter',
    'importData' => false,
    'canImportData' => false,
    'exportData' => false,
    'canExportData' => false,
])

<flux:card {{ $attributes->class('border border-(--color-background) rounded-2xl') }}>
    @if ($showSearch || count($types) > 1 || count($categories) > 1 || count($academicYears) > 0 || count($statuses) > 1 || $dateRange || $hasPages || $exportData || $importData)
        <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">

            {{-- Filters --}}
            <div class="flex-1">
                <div class="flex flex-wrap items-start gap-2">

                    @if ($showSearch)
                        <div class="w-full sm:w-56 lg:w-64">
                            <flux:input :placeholder="__('Enter keyword...')" wire:model.live.debounce.500ms="search"
                                clearable />
                        </div>
                    @endif

                    @if (count($types) > 1)
                        <div class="w-full sm:w-56 lg:w-64">
                            @if ($dateRange)
                                <flux:select wire:model.live="selectedType" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All type') }}</flux:select.option>
                                    @foreach ($types as $type)
                                        <flux:select.option value="{{ $type['value'] }}">{{ $type['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                <flux:select wire:model.lazy="selectedType" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All type') }}</flux:select.option>
                                    @foreach ($types as $type)
                                        <flux:select.option value="{{ $type['value'] }}">{{ $type['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif
                        </div>
                    @endif

                    @if (count($categories) > 1)
                        <div class="w-full sm:w-56 lg:w-64">
                            @if ($dateRange)
                                <flux:select wire:model.live="selectedCategory" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All category') }}</flux:select.option>
                                    @foreach ($categories as $category)
                                        <flux:select.option value="{{ $category['value'] }}">{{ $category['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                <flux:select wire:model.lazy="selectedCategory" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All category') }}</flux:select.option>
                                    @foreach ($categories as $category)
                                        <flux:select.option value="{{ $category['value'] }}">{{ $category['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif
                        </div>
                    @endif

                    @if (count($academicYears) > 0)
                        <div class="w-full sm:w-56 lg:w-64">
                            @if ($dateRange)
                                <flux:select wire:model.live="selectedAcademicYear" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All academic years') }}</flux:select.option>
                                    @foreach ($academicYears as $academicYear)
                                        <flux:select.option value="{{ $academicYear['value'] }}">{{ $academicYear['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                <flux:select wire:model.lazy="selectedAcademicYear" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All academic years') }}</flux:select.option>
                                    @foreach ($academicYears as $academicYear)
                                        <flux:select.option value="{{ $academicYear['value'] }}">{{ $academicYear['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif
                        </div>
                    @endif

                    @if (count($statuses) > 1)
                        <div class="w-full sm:w-56 lg:w-64">
                            @if ($dateRange)
                                <flux:select wire:model.live="selectedStatus" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All status') }}</flux:select.option>
                                    @foreach ($statuses as $status)
                                        <flux:select.option value="{{ $status['value'] }}">{{ $status['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @else
                                <flux:select wire:model.lazy="selectedStatus" variant="listbox" searchable
                                    :placeholder="__('Select...')">
                                    <flux:select.option value="">{{ __('All status') }}</flux:select.option>
                                    @foreach ($statuses as $status)
                                        <flux:select.option value="{{ $status['value'] }}">{{ $status['label'] }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            @endif
                        </div>
                    @endif

                    @if ($dateRange)
                        <div class="w-full sm:w-72 lg:w-88">
                            <flux:date-picker wire:model.live="dateRange" mode="range"
                                presets="thisMonth lastMonth thisQuarter lastQuarter thisYear lastYear yearToDate"
                                locale="vi-VN" clearable/>
                        </div>
                    @endif

                    @if ($hasPages)
                        <div class="w-full sm:w-44">
                            <flux:select variant="listbox" wire:model.lazy="perPage">
                                <flux:select.option value="15">15 {{ __('items/page') }}</flux:select.option>
                                <flux:select.option value="25">25 {{ __('items/page') }}</flux:select.option>
                                <flux:select.option value="50">50 {{ __('items/page') }}</flux:select.option>
                                <flux:select.option value="100">100 {{ __('items/page') }}</flux:select.option>
                            </flux:select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Export / Import --}}
            @if ($exportData || $importData)
                <div class="flex w-full flex-row gap-3 md:w-auto">
                    @if ($importData)
                        @if (!$canImportData || auth()->user()?->can($canImportData))
                            <flux:button wire:click="importData" icon="arrow-up-tray"
                                class="flex-1 md:flex-none">
                                {{ __('Import') }}
                            </flux:button>
                        @endif
                    @endif

                    @if ($exportData)
                        @if (!$canExportData || auth()->user()?->can($canExportData))
                            <flux:button wire:click="exportData" icon="arrow-down-tray"
                                class="flex-1 md:flex-none">
                                {{ __('Export') }}
                            </flux:button>
                        @endif
                    @endif
                </div>
            @endif

        </div>
    @endif

    @if (trim((string) $slot) !== '')
        <div
            @class([
                'mt-6' => $showSearch || count($types) > 1 || count($categories) > 1 || count($academicYears) > 0 || count($statuses) > 1 || $dateRange || $hasPages || $exportData || $importData,
            ])>
            {{ $slot }}
        </div>
    @endif

    @if ($showResetControl)
        <div wire:click="{{ $resetAction }}()"
            class="mt-2 inline-flex cursor-pointer items-center gap-1.5 group text-(--color-text-hidden) hover:text-(--color-accent-content) dark:hover:text-pink-500">
            <flux:icon.arrow-path variant="mini" class="size-4 group-hover:rotate-180" />
            <span class="text-sm font-medium">{{ __('Reset filters') }}</span>
        </div>
    @endif
</flux:card>
