<div>
    <flux:modal wire:model="showRegulationModal" class="max-w-3xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingRegulationId ? __('Edit regulation') : __('Create regulation') }}
                </flux:heading>
                <flux:text>{{ __('Manage the regulation content, points, and apply scope.') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:input wire:model.live.debounce.500ms="short_desc" :label="__('Short description')" />
                </div>
                <div class="md:col-span-2">
                    <flux:textarea wire:model.live.debounce.500ms="description" :label="__('Description')" class="min-h-28" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.500ms="point_value" :label="__('Points')" type="text"
                        inputmode="numeric" />
                </div>
                <div class="md:col-span-1">
                    <flux:select wire:model.live="type" variant="listbox" :label="__('Type')">
                        <flux:select.option value="plus">{{ __('Bonus') }}</flux:select.option>
                        <flux:select.option value="minus">{{ __('Penalty') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-1">
                    <flux:select wire:model.live="status" variant="listbox" :label="__('Status')">
                        <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                        <flux:select.option value="applied">{{ __('Applied') }}</flux:select.option>
                        <flux:select.option value="not_applied">{{ __('Not applied') }}</flux:select.option>
                    </flux:select>
                </div>
                <div class="hidden md:block md:col-span-1"></div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeRegulationModal">{{ __('Cancel') }}</flux:button>
                @if ($this->shouldShowSaveRegulationButton())
                    <flux:button variant="primary" wire:click="saveRegulation">
                        {{ $editingRegulationId ? __('Save regulation') : __('Add regulation') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete regulation') }}</flux:heading>
                <flux:text>{{ __('This regulation will be removed permanently.') }}</flux:text>
            </div>

            @error('deleteRegulation')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteRegulation">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
