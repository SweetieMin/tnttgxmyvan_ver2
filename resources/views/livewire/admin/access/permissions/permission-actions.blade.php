<div>
    <flux:modal wire:model="showPermissionModal" class="max-w-xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingPermissionId ? __('Edit permission') : __('Create permission') }}
                </flux:heading>
                <flux:text>{{ __('Use a dot notation key such as access.role.view.') }}</flux:text>
            </div>

            <flux:input wire:model.live.debounce.500ms="permissionName" :label="__('Permission key')" :placeholder="__('Enter permission key')" />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closePermissionModal">{{ __('Cancel') }}</flux:button>
                @if ($this->hasPermissionChanges())
                    <flux:button variant="primary" wire:click="savePermission">
                        {{ $editingPermissionId ? __('Save permission') : __('Add permission') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete permission') }}</flux:heading>
                <flux:text>{{ __('This permission cannot be removed while it is still assigned.') }}</flux:text>
            </div>

            @error('deletePermission')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deletePermission">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
