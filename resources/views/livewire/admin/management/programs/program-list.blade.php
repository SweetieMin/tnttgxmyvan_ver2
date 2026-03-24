<x-app-layout-table :paginator="$programs">
    @php($canUpdateProgram = auth()->user()->can('management.program.update'))

    <x-slot:desktop>
        <flux:table :paginate="$programs">
            <flux:table.columns>
                <flux:table.column>{{ __('Ordering') }}</flux:table.column>
                <flux:table.column>{{ __('Catechism class') }}</flux:table.column>
                <flux:table.column>{{ __('Sector') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            @if ($canUpdateProgram)
                <flux:table.rows wire:sort="sortProgram">
                    @forelse ($programs as $program)
                        <flux:table.row :key="$program->id" wire:key="program-{{ $program->id }}" wire:sort:item="{{ $program->id }}">
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" wire:sort:handle class="cursor-grab active:cursor-grabbing">
                                    {{ $program->ordering }}
                                </flux:button>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">{{ $program->course }}</flux:table.cell>
                            <flux:table.cell>{{ $program->sector }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('management.program.update')
                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-program', { programId: {{ $program->id }} })">
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('management.program.delete')
                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-program', { programId: {{ $program->id }} })">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="py-8 text-center">
                                {{ __('No programs found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            @else
                <flux:table.rows>
                    @forelse ($programs as $program)
                        <flux:table.row :key="$program->id" wire:key="program-{{ $program->id }}">
                            <flux:table.cell>
                                <span class="text-sm font-medium">{{ $program->ordering }}</span>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">{{ $program->course }}</flux:table.cell>
                            <flux:table.cell>{{ $program->sector }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('management.program.update')
                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-program', { programId: {{ $program->id }} })">
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('management.program.delete')
                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-program', { programId: {{ $program->id }} })">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="py-8 text-center">
                                {{ __('No programs found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            @endif
        </flux:table>
    </x-slot:desktop>

    <x-slot:mobile>
        @if ($canUpdateProgram)
            <div class="space-y-3">
                @forelse ($programs as $program)
                    <div wire:key="program-mobile-{{ $program->id }}">
                        <flux:card class="rounded-2xl p-0">
                            <flux:accordion variant="reverse">
                                <flux:accordion.item>
                                    <flux:accordion.heading class="px-4 py-3">
                                        <div class="w-full space-y-1 text-left">
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $program->course }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $program->sector }}</div>
                                        </div>
                                    </flux:accordion.heading>

                                    <flux:accordion.content class="px-4 pb-4">
                                        <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                            <div class="flex items-center justify-between gap-3 text-sm">
                                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Ordering') }}</span>
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $program->ordering }}</span>
                                            </div>

                                            @canany(['management.program.update', 'management.program.delete'])
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    @can('management.program.update')
                                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-program', { programId: {{ $program->id }} })">
                                                            {{ __('Edit') }}
                                                        </flux:button>
                                                    @endcan
                                                    @can('management.program.delete')
                                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-program', { programId: {{ $program->id }} })">
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
                    </div>
                @empty
                    <flux:card class="rounded-2xl p-6 text-center">
                        {{ __('No programs found.') }}
                    </flux:card>
                @endforelse
            </div>
        @else
            <div class="space-y-3">
                @forelse ($programs as $program)
                    <flux:card class="rounded-2xl p-0" wire:key="program-mobile-{{ $program->id }}">
                        <flux:accordion variant="reverse">
                            <flux:accordion.item>
                                <flux:accordion.heading class="px-4 py-3">
                                    <div class="w-full space-y-1 text-left">
                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $program->course }}</div>
                                        <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $program->sector }}</div>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content class="px-4 pb-4">
                                    <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Ordering') }}</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $program->ordering }}</span>
                                        </div>

                                        @canany(['management.program.update', 'management.program.delete'])
                                            <div class="flex flex-wrap justify-end gap-2">
                                                @can('management.program.update')
                                                    <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-program', { programId: {{ $program->id }} })">
                                                        {{ __('Edit') }}
                                                    </flux:button>
                                                @endcan
                                                @can('management.program.delete')
                                                    <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-program', { programId: {{ $program->id }} })">
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
                        {{ __('No programs found.') }}
                    </flux:card>
                @endforelse
            </div>
        @endif
    </x-slot:mobile>
</x-app-layout-table>
