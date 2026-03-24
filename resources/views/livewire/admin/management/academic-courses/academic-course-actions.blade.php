<div>
    <flux:modal wire:model="showAcademicCourseModal" class="max-w-3xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingAcademicCourseId ? __('Edit catechism - sector class') : ($isDuplicatingAcademicCourse ? __('Duplicate catechism - sector class') : __('Create catechism - sector class')) }}
                </flux:heading>
                <flux:text>{{ __('Choose the academic year and program, then adjust the class information for that year if needed.') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-1">
                    <flux:select wire:model.live="academic_year_id" variant="listbox" :label="__('Academic year')" searchable :placeholder="__('Select...')">
                        @foreach ($academicYears as $academicYear)
                            <flux:select.option value="{{ $academicYear->id }}">{{ $academicYear->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="md:col-span-1">
                    <flux:select wire:model.live="program_id" variant="listbox" :label="__('Program')" searchable :placeholder="__('Select...')">
                        @foreach ($programs as $program)
                            <flux:select.option value="{{ $program->id }}">{{ $program->course }} - {{ $program->sector }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="ordering" :label="__('Ordering')" type="number" min="1" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="course_name" :label="__('Catechism class')" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="sector_name" :label="__('Sector')" />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="catechism_avg_score" :label="__('Catechism average score')" type="number" step="0.01" min="0" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="catechism_training_score" :label="__('Catechism training score')" type="number" step="0.01" min="0" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.300ms="activity_score" :label="__('Activity score')" type="number" min="0" />
                </div>
            </div>

            <flux:switch wire:model.live="is_active" :label="__('Active')" />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeAcademicCourseModal">{{ __('Cancel') }}</flux:button>
                @if ($this->shouldShowSaveAcademicCourseButton())
                    <flux:button variant="primary" wire:click="saveAcademicCourse">
                        {{ $editingAcademicCourseId ? __('Save catechism - sector class') : ($isDuplicatingAcademicCourse ? __('Duplicate catechism - sector class') : __('Add catechism - sector class')) }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete catechism - sector class') }}</flux:heading>
                <flux:text>{{ __('This catechism - sector class will be removed permanently.') }}</flux:text>
            </div>

            @error('deleteAcademicCourse')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteAcademicCourse">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
