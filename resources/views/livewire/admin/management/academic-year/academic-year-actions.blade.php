<div>
    <flux:modal wire:model="showAcademicYearModal" class="max-w-5xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingAcademicYearId ? __('Edit academic year') : __('Create academic year') }}
                </flux:heading>
                <flux:text>{{ __('Manage schedules, required scores, and the current academic year status.') }}</flux:text>
            </div>

            @if (!$showAcademicYearDetails)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <flux:select wire:model.live="start_year" variant="listbox" :label="__('Start year')">
                            @foreach ($this->yearOptions() as $year)
                                <flux:select.option :value="$year">{{ $year }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="sm:col-span-1">
                        <flux:select wire:model.live="end_year" variant="listbox" :label="__('End year')">
                            @foreach ($this->yearOptions() as $year)
                                <flux:select.option :value="$year">{{ $year }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeAcademicYearModal">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" wire:click="continueToAcademicYearDetails">
                        {{ __('Continue') }}
                    </flux:button>
                </div>
            @else
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4 lg:items-end">
                        <div class="lg:col-span-1">
                            <flux:input :value="$this->generatedAcademicYearName()" :label="__('Academic year code')" readonly />
                        </div>

                        <div class="lg:col-span-1 lg:col-start-4">
                            <flux:select wire:model.live="status_academic" variant="listbox" :label="__('Status')">
                                @foreach ($this->statusOptions() as $value => $label)
                                    <flux:select.option :value="$value">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
                        <div class="xl:col-span-1">
                            <flux:date-picker wire:model.live="catechism_start_date" :label="__('Catechism start date')" type="input" locale="vi-VN" />
                        </div>
                        <div class="xl:col-span-1">
                            <flux:date-picker wire:model.live="catechism_end_date" :label="__('Catechism end date')" type="input" locale="vi-VN" />
                        </div>
                        <div class="xl:col-span-1">
                            <flux:date-picker wire:model.live="activity_start_date" :label="__('Activity start date')" type="input" locale="vi-VN" />
                        </div>
                        <div class="xl:col-span-1">
                            <flux:date-picker wire:model.live="activity_end_date" :label="__('Activity end date')" type="input" locale="vi-VN" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
                        <div class="xl:col-span-1">
                            <flux:input wire:model.live.debounce.500ms="catechism_avg_score" :label="__('Catechism average score')" type="number" step="0.01" min="0" />
                        </div>
                        <div class="xl:col-span-1">
                            <flux:input wire:model.live.debounce.500ms="catechism_training_score" :label="__('Catechism training score')" type="number" step="0.01" min="0" />
                        </div>
                        <div class="xl:col-span-1">
                            <flux:input wire:model.live.debounce.500ms="activity_score" :label="__('Activity score')" type="number" min="0" />
                        </div>
                        <div class="xl:col-span-1"></div>
                    </div>

                    <flux:field variant="inline">
                        <flux:checkbox wire:model.live="should_create_academic_courses" />
                        <flux:label>{{ __('Automatically create or sync catechism - sector classes from programs') }}</flux:label>
                    </flux:field>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeAcademicYearModal">{{ __('Cancel') }}</flux:button>
                    @if ($editingAcademicYearId === null)
                        <flux:button variant="filled" wire:click="backToAcademicYearSelection">
                            {{ __('Change academic year') }}
                        </flux:button>
                    @endif
                    @if ($this->shouldShowSaveAcademicYearButton())
                        <flux:button variant="primary" wire:click="saveAcademicYear">
                            {{ $editingAcademicYearId ? __('Save academic year') : __('Add academic year') }}
                        </flux:button>
                    @endif
                </div>
            @endif
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete academic year') }}</flux:heading>
                <flux:text>{{ __('This academic year will be removed permanently.') }}</flux:text>
            </div>

            @error('deleteAcademicYear')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteAcademicYear">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showSyncAcademicCoursesConfirmModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Sync catechism - sector classes') }}</flux:heading>
                <flux:text>{{ __('This academic year already has catechism - sector classes. If you continue, they will be updated to match the current programs and score settings.') }}</flux:text>
            </div>

            <flux:callout variant="warning" icon="exclamation-triangle" :heading="__('Existing manual adjustments will be replaced by the current program structure.')" />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeSyncAcademicCoursesConfirmModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="confirmSyncAcademicCoursesAndSave">{{ __('Agree and sync') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
