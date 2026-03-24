<x-app-layout-table :paginator="$permissions">
    <x-slot:desktop>
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
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse ($permissions as $permission)
            <flux:card class="rounded-2xl p-0" wire:key="permission-mobile-{{ $permission->id }}">
                <flux:accordion variant="reverse">
                    <flux:accordion.item>
                        <flux:accordion.heading class="px-4 py-3">
                            <div class="w-full space-y-1 text-left">
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ str($permission->name)->replace('.', ' ')->replace('-', ' ')->headline()->toString() }}
                                </div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $permission->name }}</div>
                            </div>
                        </flux:accordion.heading>

                        <flux:accordion.content class="px-4 pb-4">
                            <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="space-y-1">
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Group') }}</div>
                                        <flux:badge size="sm" color="zinc">
                                            {{ str(explode('.', $permission->name)[1] ?? explode('.', $permission->name)[0] ?? __('General'))->replace('-', ' ')->headline()->toString() }}
                                        </flux:badge>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Roles') }}</div>
                                        <flux:badge size="sm" color="sky">{{ $permission->roles_count }}</flux:badge>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Users') }}</div>
                                        <flux:badge size="sm" color="emerald">{{ $permission->users_count }}</flux:badge>
                                    </div>
                                </div>

                                @canany(['access.permission.update', 'access.permission.delete'])
                                    <div class="flex flex-wrap justify-end gap-2">
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
                                @endcanany
                            </div>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </flux:card>
        @empty
            <flux:card class="rounded-2xl p-6 text-center">
                {{ __('No permissions found.') }}
            </flux:card>
        @endforelse
    </x-slot:mobile>
</x-app-layout-table>
