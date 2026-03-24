<x-app-layout-table :paginator="$roles">
    <x-slot:desktop>
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
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse ($roles as $role)
            <flux:card class="rounded-2xl p-0" wire:key="role-mobile-{{ $role->id }}">
                <flux:accordion variant="reverse">
                    <flux:accordion.item>
                        <flux:accordion.heading class="px-4 py-3">
                            <div class="flex w-full items-start justify-between gap-3 text-left">
                                <div class="font-semibold text-zinc-900 dark:text-white">{{ $role->name }}</div>
                                <flux:badge size="sm" color="sky">{{ $role->users_count }}</flux:badge>
                            </div>
                        </flux:accordion.heading>

                        <flux:accordion.content class="px-4 pb-4">
                            <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <div class="grid grid-cols-3 gap-3 text-sm">
                                    <div class="space-y-1">
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Permissions') }}</div>
                                        <flux:badge size="sm" color="zinc">{{ $role->permissions_count }}</flux:badge>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Managed roles') }}</div>
                                        <flux:badge size="sm" color="amber">{{ $role->manageable_roles_count }}</flux:badge>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Users') }}</div>
                                        <flux:badge size="sm" color="sky">{{ $role->users_count }}</flux:badge>
                                    </div>
                                </div>

                                @canany(['access.role.update', 'access.role.delete'])
                                    <div class="flex flex-wrap justify-end gap-2">
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
                                @endcanany
                            </div>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </flux:card>
        @empty
            <flux:card class="rounded-2xl p-6 text-center">
                {{ __('No roles found.') }}
            </flux:card>
        @endforelse
    </x-slot:mobile>
</x-app-layout-table>
