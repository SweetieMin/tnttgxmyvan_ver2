<div>
    <flux:modal name="attendance-schedule-form" class="max-w-3xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingAttendanceScheduleId ? __('Edit attendance schedule') : __('Create attendance schedule') }}
                </flux:heading>
                <flux:text>{{ __('Manage the session date, time range, regulation source, and attendance points.') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="lg:col-span-1">
                    <flux:select wire:model.live="academic_year_id" variant="listbox" :label="__('Academic year')">
                        @foreach ($this->academicYears() as $academicYear)
                            <flux:select.option :value="$academicYear->id">{{ $academicYear->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="lg:col-span-1">
                    <flux:switch wire:model.live="is_active" :label="__('Active')" />
                </div>

                <div class="lg:col-span-1">
                    <flux:date-picker wire:model.live="attendance_date" :label="__('Attendance date')" locale="vi-VN" selectable-header />
                </div>

                <div class="lg:col-span-1">
                    <flux:select wire:model.live="regulation_id" variant="combobox" :label="__('Regulation')">
                        <flux:select.option value="">{{ __('No regulation') }}</flux:select.option>
                        @foreach ($this->regulations() as $regulation)
                            <flux:select.option :value="$regulation->id">
                                {{ $regulation->description }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="lg:col-span-2">
                    <flux:input wire:model.live="title" :label="__('Schedule title')" readonly />
                </div>

                <div class="lg:col-span-1">
                    <flux:time-picker wire:model.live="start_time" :label="__('Start time')" type="input" locale="vi-VN" />
                </div>

                <div class="lg:col-span-1">
                    <flux:time-picker wire:model.live="end_time" :label="__('End time')" type="input" locale="vi-VN" />
                </div>

                <div class="lg:col-span-1">
                    <flux:input wire:model.live.debounce.500ms="points" :label="__('Points')" type="number" min="0" />
                </div>
            </div>

            <div class="flex justify-between gap-3">
                <div>
                    @if ($editingAttendanceScheduleId)
                        <flux:button variant="danger" wire:click="confirmDeleteAttendanceSchedule">
                            {{ __('Delete attendance schedule') }}
                        </flux:button>
                    @endif
                </div>

                <div class="flex gap-3">
                    <flux:button variant="ghost" wire:click="closeAttendanceScheduleModal">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" wire:click="saveAttendanceSchedule">
                        {{ $editingAttendanceScheduleId ? __('Save attendance schedule') : __('Add attendance schedule') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="delete-attendance-schedule" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete attendance schedule') }}</flux:heading>
                <flux:text>{{ __('This attendance schedule will be removed permanently.') }}</flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteAttendanceSchedule">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
