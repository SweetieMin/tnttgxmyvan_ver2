<flux:card class="overflow-hidden rounded-2xl p-0">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="bg-zinc-50 dark:bg-zinc-900/60">
                <tr class="text-left text-sm text-zinc-500 dark:text-zinc-400">
                    <th class="px-4 py-3 font-medium">{{ __('Permission') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Group') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Roles') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Users') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($permissions as $permission)
                    <tr wire:key="permission-row-{{ $permission->id }}" class="bg-white dark:bg-zinc-950/20">
                        <td class="px-4 py-4">
                            <div class="font-medium text-zinc-900 dark:text-white">
                                {{ str($permission->name)->replace('.', ' ')->replace('-', ' ')->headline()->toString() }}
                            </div>
                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $permission->name }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <flux:badge size="sm" color="zinc">
                                {{ str(explode('.', $permission->name)[1] ?? explode('.', $permission->name)[0] ?? __('General'))->replace('-', ' ')->headline()->toString() }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-4">
                            <flux:badge size="sm" color="sky">{{ $permission->roles_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-4">
                            <flux:badge size="sm" color="emerald">{{ $permission->users_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-4">
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No permissions found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($permissions->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">
            {{ $permissions->links() }}
        </div>
    @endif
</flux:card>
