<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$roles">
        <flux:table.columns>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <flux:table.column>{{ __('Permissions') }}</flux:table.column>
            <flux:table.column>{{ __('Managed roles') }}</flux:table.column>
            <flux:table.column>{{ __('Users') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($roles as $role)
                <flux:table.row :key="$role->id">
                    <flux:table.cell variant="strong">{{ $role->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $role->permissions_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="amber">{{ $role->manageable_roles_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="sky">{{ $role->users_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            @can('access.role.update')
                                <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-role', { roleId: {{ $role->id }} })">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan
                            @can('access.role.delete')
                                <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-role', { roleId: {{ $role->id }} })">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-8 text-center">
                        {{ __('No roles found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
