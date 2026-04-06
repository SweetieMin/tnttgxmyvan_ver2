<div>
    <flux:modal wire:model="showExportModal" class="max-w-md">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Export transactions') }}</flux:heading>
                <flux:text>{{ __('Choose filters and columns before exporting the common fund report to Excel.') }}
                </flux:text>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_1fr]">
                <div class="space-y-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <flux:heading size="sm">{{ __('Transaction type') }}</flux:heading>
                            <div class="flex items-center gap-3">
                                <flux:badge color="sky">{{ count($selectedTypes) }} {{ __('selected') }}</flux:badge>
                                <flux:button size="sm" variant="ghost" wire:click="selectAllTypes">
                                    {{ __('Select all') }}
                                </flux:button>
                            </div>
                        </div>

                        <flux:checkbox.group wire:model.live="selectedTypes" class="grid gap-3 sm:grid-cols-2">
                            @foreach ($this->typeOptions() as $type)
                                <label wire:key="export-type-{{ $type['value'] }}"
                                    class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                    <flux:checkbox value="{{ $type['value'] }}" />
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $type['label'] }}</div>
                                </label>
                            @endforeach
                        </flux:checkbox.group>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <flux:heading size="sm">{{ __('Category') }}</flux:heading>
                            <div class="flex items-center gap-3">
                                <flux:badge color="sky">{{ count($selectedCategoryIds) }} {{ __('selected') }}
                                </flux:badge>
                                <flux:button size="sm" variant="ghost" wire:click="selectAllCategories">
                                    {{ __('Select all') }}
                                </flux:button>
                                <flux:button size="sm" variant="danger" wire:click="clearSelectedCategories">
                                    {{ __('Clear all') }}
                                </flux:button>
                            </div>
                        </div>

                        <flux:checkbox.group wire:model.live="selectedCategoryIds"
                            class="max-h-[220px] space-y-3 overflow-y-auto rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                            @foreach ($this->categoryOptions() as $category)
                                <label wire:key="export-category-{{ $category['value'] }}"
                                    class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                    <flux:checkbox value="{{ $category['value'] }}" />
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $category['label'] }}
                                    </div>
                                </label>
                            @endforeach
                        </flux:checkbox.group>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <flux:heading size="sm">{{ __('Status') }}</flux:heading>
                            <div class="flex items-center gap-3">
                                <flux:badge color="sky">{{ count($selectedStatuses) }} {{ __('selected') }}
                                </flux:badge>
                                <flux:button size="sm" variant="ghost" wire:click="selectAllStatuses">
                                    {{ __('Select all') }}
                                </flux:button>
                            </div>
                        </div>

                        <flux:checkbox.group wire:model.live="selectedStatuses" class="grid gap-3 sm:grid-cols-2">
                            @foreach ($this->statusOptions() as $status)
                                <label wire:key="export-status-{{ $status['value'] }}"
                                    class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                    <flux:checkbox value="{{ $status['value'] }}" />
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $status['label'] }}</div>
                                </label>
                            @endforeach
                        </flux:checkbox.group>
                    </div>


                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-4">
                        <flux:heading size="sm">{{ __('Columns to export') }}</flux:heading>
                        <div class="flex items-center gap-3">
                            <flux:badge color="sky">{{ count($selectedColumns) }} {{ __('selected') }}</flux:badge>
                            <flux:button size="sm" variant="ghost" wire:click="selectAllColumns">
                                {{ __('Select all') }}
                            </flux:button>
                        </div>
                    </div>

                    <flux:checkbox.group wire:model.live="selectedColumns"
                        class="space-y-3 rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                        @foreach ($this->availableColumns() as $column => $label)
                            <label wire:key="export-column-{{ $column }}"
                                class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                <flux:checkbox value="{{ $column }}" />
                                <div class="min-w-0">
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $label }}</div>
                                    @if ($column === 'amount')
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ __('Amount will be split into Income and Expense columns in Excel.') }}
                                        </div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </flux:checkbox.group>

                    @error('selectedColumns')
                        <flux:callout variant="danger" icon="x-circle" :heading="$message" />
                    @enderror
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-3">
                    <flux:date-picker wire:model.live="dateRange" mode="range" :label="__('Date range')"
                        presets="thisMonth lastMonth thisQuarter lastQuarter thisYear lastYear yearToDate"
                        locale="vi-VN" />

                    <flux:callout class="mb-2" variant="warning" icon="exclamation-circle"
                        :heading="__('Leave the filters empty to export all transactions.')" />
                </div>

                <div class="space-y-3">
                    <flux:input.group :label="__('File name')">
                        <flux:input wire:model.live="fileName" :placeholder="__('Common fund report')" />
                        <flux:input.group.suffix>.xlsx</flux:input.group.suffix>
                    </flux:input.group>

                    @error('fileName')
                        <flux:callout variant="danger" icon="x-circle" :heading="$message" />
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeExportModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="exportTransactions" icon="arrow-down-tray">
                    {{ __('Export Excel') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
