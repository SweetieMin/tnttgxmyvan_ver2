<flux:card class="overflow-hidden rounded-2xl p-0">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
            <thead class="bg-zinc-50 dark:bg-zinc-900/60">
                <tr class="text-left text-sm text-zinc-500 dark:text-zinc-400">
                    <th class="px-4 py-3 font-medium">{{ __('Role') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Permissions') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Users') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                @forelse ($roles as $role)
                    <tr wire:key="role-row-{{ $role->id }}" class="bg-white dark:bg-zinc-950/20">
                        <td class="px-4 py-4">
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $role->name }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <flux:badge size="sm" color="zinc">{{ $role->permissions_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-4">
                            <flux:badge size="sm" color="sky">{{ $role->users_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-4">
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('No roles found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($roles->hasPages())
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-800">
            {{ $roles->links() }}
        </div>
    @endif
</flux:card>
