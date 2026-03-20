<div>
    <flux:modal wire:model="showRoleModal" class="max-w-3xl">
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">
                    {{ $editingRoleId ? __('Edit role') : __('Create role') }}
                </flux:heading>
                <flux:text>{{ __('Manage the role name and choose the permissions it should have.') }}</flux:text>
            </div>

            <flux:input wire:model="roleName" :label="__('Role name')" :placeholder="__('Enter role name')" />

            <div class="flex items-center justify-between gap-4">
                <flux:heading size="sm">{{ __('Permissions') }}</flux:heading>
                <flux:badge color="sky">{{ count($selectedPermissions) }} {{ __('selected') }}</flux:badge>
            </div>

            <flux:input wire:model.live.debounce.300ms="permissionSearch" :placeholder="__('Search permissions...')" />

            <div class="max-h-[420px] space-y-5 overflow-y-auto rounded-2xl border border-zinc-200 p-4 dark:border-zinc-800">
                @forelse ($permissionGroups as $group => $permissions)
                    <div wire:key="permission-group-{{ $group }}" class="space-y-3">
                        <flux:checkbox.group wire:model="selectedPermissions" class="space-y-3">
                            <div class="flex items-center justify-between gap-4">
                                <flux:heading size="sm">{{ $group }}</flux:heading>

                                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                                    <flux:checkbox.all />
                                    <span>{{ __('Select all') }}</span>
                                </label>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach ($permissions as $permission)
                                    <label wire:key="role-permission-{{ $permission->id }}" class="flex items-start gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-800">
                                        <flux:checkbox value="{{ $permission->name }}" />
                                        <div class="min-w-0">
                                            <div class="font-medium text-zinc-900 dark:text-white">
                                                {{ $this->formatPermissionLabel($permission->name) }}
                                            </div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $permission->name }}
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </flux:checkbox.group>
                    </div>
                @empty
                    <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('No permissions match your search.') }}</div>
                @endforelse
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeRoleModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="saveRole">{{ __('Save role') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete role') }}</flux:heading>
                <flux:text>{{ __('This action cannot be undone if the role is no longer needed.') }}</flux:text>
            </div>

            @error('deleteRole')
                <flux:callout variant="danger" icon="x-circle" :heading="$message" />
            @enderror

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeDeleteModal">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteRole">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
