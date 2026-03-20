<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$permissions">
        <flux:table.columns>
            <flux:table.column>{{ __('Permission') }}</flux:table.column>
            <flux:table.column>{{ __('Group') }}</flux:table.column>
            <flux:table.column>{{ __('Roles') }}</flux:table.column>
            <flux:table.column>{{ __('Users') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($permissions as $permission)
                <flux:table.row :key="$permission->id">
                    <flux:table.cell>
                        <div class="font-medium text-zinc-900 dark:text-white">
                            {{ str($permission->name)->replace('.', ' ')->replace('-', ' ')->headline()->toString() }}
                        </div>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $permission->name }}</div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">
                            {{ str(explode('.', $permission->name)[1] ?? explode('.', $permission->name)[0] ?? __('General'))->replace('-', ' ')->headline()->toString() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="sky">{{ $permission->roles_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="emerald">{{ $permission->users_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            @can('access.permission.update')
                                <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-permission', { permissionId: {{ $permission->id }} })">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan
                            @can('access.permission.delete')
                                <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-permission', { permissionId: {{ $permission->id }} })">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-8 text-center">
                        {{ __('No permissions found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
