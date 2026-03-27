<div class="space-y-4">
    <x-app-layout-table :paginator="$users">
        <x-slot:desktop>
            <flux:table :paginate="$users">
                <flux:table.columns>
                    <flux:table.column>{{ __('Profile') }}</flux:table.column>
                    <flux:table.column>{{ __('Main role') }}</flux:table.column>
                    <flux:table.column>{{ __('Phone') }}</flux:table.column>
                    <flux:table.column>{{ $group === 'deleted-users' ? __('Deleted on') : __('Status') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($users as $user)
                        <flux:table.row :key="$user->id" wire:key="personnel-user-{{ $user->id }}">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="xl" circle :src="data_get($user, 'details.picture') ?: asset('/storage/images/users/default-avatar.png')" />
                                    <div class="min-w-0">
                                        @if (filled($user->christian_name))
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $user->christian_name }}
                                        </div>
                                    @endif
                                        <div class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $user->full_name }}
                                        </div>

                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $this->mainRole($user) }}</flux:table.cell>
                            <flux:table.cell>{{ data_get($user, 'details.phone') ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($group === 'deleted-users')
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $user->deleted_at?->format('d/m/Y H:i') ?? '—' }}
                                    </div>
                                @else
                                    <div class="flex flex-wrap gap-2">
                                        <flux:badge size="sm" :color="$this->userStatusColor($user)">
                                            {{ $this->userStatusLabel($user) }}
                                        </flux:badge>

                                        @if ($group === 'children')
                                            <flux:badge size="sm" :color="$this->childStudyStatusColor($user)">
                                                {{ $this->childStudyStatusLabel($user) }}
                                            </flux:badge>
                                        @endif
                                    </div>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @if ($this->canUpdateUser($user) || $this->canDeleteUser($user))
                                        <flux:dropdown position="bottom end">
                                            <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
                                                {{ __('Options') }}
                                            </flux:button>

                                            <flux:menu>
                                                @if ($this->canUpdateUser($user))
                                                    <flux:menu.item icon="pencil-square" :href="$this->editRoute($user)" wire:navigate>
                                                        {{ __('Edit') }}
                                                    </flux:menu.item>
                                                @endif

                                                @if ($this->canDeleteUser($user))
                                                    <flux:menu.separator />
                                                    <flux:menu.item variant="danger" icon="trash"
                                                        wire:click="confirmDeleteUser({{ $user->id }})">
                                                        {{ __('Delete') }}
                                                    </flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center">
                                {{ __('No personnel found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </x-slot:desktop>

        <x-slot:mobile>
            <div class="space-y-3">
                @forelse ($users as $user)
                    <flux:card class="rounded-2xl p-0" wire:key="personnel-mobile-user-{{ $user->id }}">
                        <flux:accordion variant="reverse">
                            <flux:accordion.item>
                                <flux:accordion.heading class="px-4 py-3">
                                    <div class="flex w-full items-center gap-3 text-left">
                                        <flux:avatar circle :src="data_get($user, 'details.picture') ?: asset('/storage/images/users/default-avatar.png')" />
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-zinc-900 dark:text-white">
                                                {{ $user->full_name }}
                                            </div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $this->mainRole($user) }}
                                            </div>
                                        </div>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content class="px-4 pb-4">
                                    <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</div>
                                                <div class="font-medium text-zinc-900 dark:text-white">
                                                    {{ data_get($user, 'details.phone') ?: '—' }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Profile code') }}</div>
                                                <div class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $user->username }}
                                                </div>
                                            </div>
                                        </div>

                                        @if ($group === 'deleted-users')
                                            <div class="text-sm">
                                                <div class="text-zinc-500 dark:text-zinc-400">{{ __('Deleted on') }}</div>
                                                <div class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $user->deleted_at?->format('d/m/Y H:i') ?? '—' }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex flex-wrap gap-2">
                                                <flux:badge size="sm" :color="$this->userStatusColor($user)">
                                                    {{ $this->userStatusLabel($user) }}
                                                </flux:badge>

                                                @if ($group === 'children')
                                                    <flux:badge size="sm" :color="$this->childStudyStatusColor($user)">
                                                        {{ $this->childStudyStatusLabel($user) }}
                                                    </flux:badge>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="flex justify-end">
                                            <div class="flex gap-2">
                                                @if ($this->canUpdateUser($user) || $this->canDeleteUser($user))
                                                    <flux:dropdown position="bottom end">
                                                        <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
                                                            {{ __('Options') }}
                                                        </flux:button>

                                                        <flux:menu>
                                                            @if ($this->canUpdateUser($user))
                                                                <flux:menu.item icon="pencil-square" :href="$this->editRoute($user)" wire:navigate>
                                                                    {{ __('Edit') }}
                                                                </flux:menu.item>
                                                            @endif

                                                            @if ($this->canDeleteUser($user))
                                                                <flux:menu.separator />
                                                                <flux:menu.item variant="danger" icon="trash"
                                                                    wire:click="confirmDeleteUser({{ $user->id }})">
                                                                    {{ __('Delete') }}
                                                                </flux:menu.item>
                                                            @endif
                                                        </flux:menu>
                                                    </flux:dropdown>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        </flux:accordion>
                    </flux:card>
                @empty
                    <flux:card class="rounded-2xl p-6 text-center">
                        {{ __('No personnel found.') }}
                    </flux:card>
                @endforelse
            </div>
        </x-slot:mobile>
    </x-app-layout-table>

    <flux:modal wire:model="showDeleteModal" class="max-w-lg">
        <div class="space-y-5">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete personnel profile') }}</flux:heading>
                <flux:text>{{ __('This personnel profile will be moved out of the active list.') }}</flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteUser">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
