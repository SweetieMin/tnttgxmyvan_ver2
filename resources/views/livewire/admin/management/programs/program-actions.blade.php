<div>
    <flux:modal wire:model="showProgramModal" class="max-w-xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingProgramId ? __('Edit program') : __('Create program') }}
                </flux:heading>
                <flux:text>{{ __('Manage the catechism class, sector, and display order from the list.') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.500ms="course" :label="__('Catechism class')" />
                </div>
                <div class="md:col-span-1">
                    <flux:input wire:model.live.debounce.500ms="sector" :label="__('Sector')" />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeProgramModal">{{ __('Cancel') }}</flux:button>
                @if ($this->shouldShowSaveProgramButton())
                    <flux:button variant="primary" wire:click="saveProgram">
                        {{ $editingProgramId ? __('Save program') : __('Add program') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete program') }}</flux:heading>
                <flux:text>{{ __('This program will be removed permanently.') }}</flux:text>
            </div>

            @error('deleteProgram')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteProgram">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
