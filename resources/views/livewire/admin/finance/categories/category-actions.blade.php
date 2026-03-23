<div>
    <flux:modal wire:model="showCategoryModal" class="max-w-xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingCategoryId ? __('Edit category') : __('Create category') }}
                </flux:heading>
                <flux:text>{{ __('Manage category name, note, active status, and display order from the list.') }}</flux:text>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <flux:input wire:model.live.debounce.500ms="name" :label="__('Category')" />
                <flux:textarea wire:model.live.debounce.500ms="description" :label="__('Description')" rows="3" />
                <flux:switch wire:model.live="is_active" :label="__('Active')" />
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeCategoryModal">{{ __('Cancel') }}</flux:button>
                @if ($this->shouldShowSaveCategoryButton())
                    <flux:button variant="primary" wire:click="saveCategory">
                        {{ $editingCategoryId ? __('Save category') : __('Add category') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete category') }}</flux:heading>
                <flux:text>{{ __('This category will be removed permanently.') }}</flux:text>
            </div>

            @error('deleteCategory')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteCategory">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
