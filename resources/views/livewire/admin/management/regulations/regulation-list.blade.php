<x-app-layout-table :paginator="$regulations">
    @php($canUpdateRegulation = auth()->user()->can('management.regulation.update'))

    <x-slot:desktop>
        <flux:table :paginate="$regulations">
            <flux:table.columns>
                <flux:table.column>{{ __('Ordering') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('Type') }}</flux:table.column>
                <flux:table.column>{{ __('Points') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            @if ($canUpdateRegulation)
                <flux:table.rows wire:sort="sortRegulation">
                    @forelse ($regulations as $regulation)
                        <flux:table.row :key="$regulation->id" wire:key="regulation-{{ $regulation->id }}" wire:sort:item="{{ $regulation->id }}">
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" wire:sort:handle class="cursor-grab active:cursor-grabbing">
                                    {{ $regulation->ordering }}
                                </flux:button>
                            </flux:table.cell>
                            <flux:table.cell class="max-w-xl">
                                <div class="line-clamp-2">{{ $regulation->description }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$regulation->type_color">
                                    {{ __($regulation->type_label) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $regulation->points }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$regulation->status_color">
                                    {{ __($regulation->status_label) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('management.regulation.update')
                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-regulation', { regulationId: {{ $regulation->id }} })">
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('management.regulation.delete')
                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-regulation', { regulationId: {{ $regulation->id }} })">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="py-8 text-center">
                                {{ __('No regulations found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            @else
                <flux:table.rows>
                    @forelse ($regulations as $regulation)
                        <flux:table.row :key="$regulation->id" wire:key="regulation-{{ $regulation->id }}">
                            <flux:table.cell>
                                <span class="text-sm font-medium">{{ $regulation->ordering }}</span>
                            </flux:table.cell>
                            <flux:table.cell class="max-w-xl">
                                <div class="line-clamp-2">{{ $regulation->description }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$regulation->type_color">
                                    {{ __($regulation->type_label) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $regulation->points }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$regulation->status_color">
                                    {{ __($regulation->status_label) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('management.regulation.update')
                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-regulation', { regulationId: {{ $regulation->id }} })">
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('management.regulation.delete')
                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-regulation', { regulationId: {{ $regulation->id }} })">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="py-8 text-center">
                                {{ __('No regulations found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            @endif
        </flux:table>
    </x-slot:desktop>

    <x-slot:mobile>
        @if ($canUpdateRegulation)
            <div class="space-y-3">
                @forelse ($regulations as $regulation)
                    <div wire:key="regulation-mobile-{{ $regulation->id }}">
                        <flux:card class="rounded-2xl p-0">
                            <flux:accordion variant="reverse">
                                <flux:accordion.item>
                                    <flux:accordion.heading class="px-4 py-3">
                                        <div class="flex w-full items-start justify-between gap-3 text-left">
                                            <div class="min-w-0 flex-1">
                                                <div class="line-clamp-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                                    {{ $regulation->description }}
                                                </div>
                                            </div>
                                            <div class="shrink-0 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                                {{ $regulation->ordering }}
                                            </div>
                                        </div>
                                    </flux:accordion.heading>

                                    <flux:accordion.content class="px-4 pb-4">
                                        <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <flux:badge size="sm" :color="$regulation->status_color">
                                                    {{ __($regulation->status_label) }}
                                                </flux:badge>
                                                <flux:badge size="sm" :color="$regulation->type_color">
                                                    {{ __($regulation->type_label) }}
                                                </flux:badge>
                                                <flux:badge size="sm" color="zinc">{{ __('Points') }}: {{ $regulation->points }}</flux:badge>
                                            </div>

                                            @canany(['management.regulation.update', 'management.regulation.delete'])
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    @can('management.regulation.update')
                                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-regulation', { regulationId: {{ $regulation->id }} })">
                                                            {{ __('Edit') }}
                                                        </flux:button>
                                                    @endcan
                                                    @can('management.regulation.delete')
                                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-regulation', { regulationId: {{ $regulation->id }} })">
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
                        {{ __('No regulations found.') }}
                    </flux:card>
                @endforelse
            </div>
        @else
            <div class="space-y-3">
                @forelse ($regulations as $regulation)
                    <flux:card class="rounded-2xl p-0" wire:key="regulation-mobile-{{ $regulation->id }}">
                        <flux:accordion variant="reverse">
                            <flux:accordion.item>
                                <flux:accordion.heading class="px-4 py-3">
                                    <div class="flex w-full items-start justify-between gap-3 text-left">
                                        <div class="min-w-0 flex-1">
                                            <div class="line-clamp-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                                {{ $regulation->description }}
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $regulation->ordering }}</span>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content class="px-4 pb-4">
                                    <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <flux:badge size="sm" :color="$regulation->status_color">
                                                {{ __($regulation->status_label) }}
                                            </flux:badge>
                                            <flux:badge size="sm" :color="$regulation->type_color">
                                                {{ __($regulation->type_label) }}
                                            </flux:badge>
                                            <flux:badge size="sm" color="zinc">{{ __('Points') }}: {{ $regulation->points }}</flux:badge>
                                        </div>

                                        @canany(['management.regulation.update', 'management.regulation.delete'])
                                            <div class="flex flex-wrap justify-end gap-2">
                                                @can('management.regulation.update')
                                                    <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-regulation', { regulationId: {{ $regulation->id }} })">
                                                        {{ __('Edit') }}
                                                    </flux:button>
                                                @endcan
                                                @can('management.regulation.delete')
                                                    <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-regulation', { regulationId: {{ $regulation->id }} })">
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
                        {{ __('No regulations found.') }}
                    </flux:card>
                @endforelse
            </div>
        @endif
    </x-slot:mobile>
</x-app-layout-table>
