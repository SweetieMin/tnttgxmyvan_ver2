<x-app-layout-table :paginator="$categories">
    @php($canUpdateCategory = auth()->user()->can('finance.category.update'))

    <x-slot:desktop>
        <flux:table :paginate="$categories">
            <flux:table.columns>
                <flux:table.column>{{ __('Ordering') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            @if ($canUpdateCategory)
                <flux:table.rows wire:sort="sortCategory">
                    @forelse ($categories as $category)
                        <flux:table.row :key="$category->id" wire:key="category-{{ $category->id }}" wire:sort:item="{{ $category->id }}">
                            <flux:table.cell>
                                <flux:button size="sm" variant="ghost" wire:sort:handle class="cursor-grab active:cursor-grabbing">
                                    {{ $category->ordering }}
                                </flux:button>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">{{ $category->name }}</flux:table.cell>
                            <flux:table.cell>{{ $category->description ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$category->is_active ? 'emerald' : 'zinc'">
                                    {{ $category->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('finance.category.update')
                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-category', { categoryId: {{ $category->id }} })">
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('finance.category.delete')
                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-category', { categoryId: {{ $category->id }} })">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center">
                                {{ __('No categories found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            @else
                <flux:table.rows>
                    @forelse ($categories as $category)
                        <flux:table.row :key="$category->id" wire:key="category-{{ $category->id }}">
                            <flux:table.cell>
                                <span class="text-sm font-medium">{{ $category->ordering }}</span>
                            </flux:table.cell>
                            <flux:table.cell variant="strong">{{ $category->name }}</flux:table.cell>
                            <flux:table.cell>{{ $category->description ?: '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$category->is_active ? 'emerald' : 'zinc'">
                                    {{ $category->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex justify-end gap-2">
                                    @can('finance.category.update')
                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-category', { categoryId: {{ $category->id }} })">
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan
                                    @can('finance.category.delete')
                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-category', { categoryId: {{ $category->id }} })">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center">
                                {{ __('No categories found.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            @endif
        </flux:table>
    </x-slot:desktop>

    <x-slot:mobile>
        @if ($canUpdateCategory)
            <div class="space-y-3">
                @forelse ($categories as $category)
                    <div wire:key="category-mobile-{{ $category->id }}">
                        <flux:card class="rounded-2xl p-0">
                            <flux:accordion variant="reverse">
                                <flux:accordion.item>
                                    <flux:accordion.heading class="px-4 py-3">
                                        <div class="flex w-full items-start justify-between gap-3 text-left">
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $category->name }}</div>
                                            <flux:badge size="sm" :color="$category->is_active ? 'emerald' : 'zinc'">
                                                {{ $category->is_active ? __('Active') : __('Inactive') }}
                                            </flux:badge>
                                        </div>
                                    </flux:accordion.heading>

                                    <flux:accordion.content class="px-4 pb-4">
                                        <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                            <div class="flex items-center justify-between gap-3 text-sm">
                                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Ordering') }}</span>
                                                <span class="font-medium text-zinc-900 dark:text-white">{{ $category->ordering }}</span>
                                            </div>

                                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                                {{ $category->description ?: '—' }}
                                            </div>

                                            @canany(['finance.category.update', 'finance.category.delete'])
                                                <div class="flex flex-wrap justify-end gap-2">
                                                    @can('finance.category.update')
                                                        <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-category', { categoryId: {{ $category->id }} })">
                                                            {{ __('Edit') }}
                                                        </flux:button>
                                                    @endcan
                                                    @can('finance.category.delete')
                                                        <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-category', { categoryId: {{ $category->id }} })">
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
                        {{ __('No categories found.') }}
                    </flux:card>
                @endforelse
            </div>
        @else
            <div class="space-y-3">
                @forelse ($categories as $category)
                    <flux:card class="rounded-2xl p-0" wire:key="category-mobile-{{ $category->id }}">
                        <flux:accordion variant="reverse">
                            <flux:accordion.item>
                                <flux:accordion.heading class="px-4 py-3">
                                    <div class="flex w-full items-start justify-between gap-3 text-left">
                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $category->name }}</div>
                                        <flux:badge size="sm" :color="$category->is_active ? 'emerald' : 'zinc'">
                                            {{ $category->is_active ? __('Active') : __('Inactive') }}
                                        </flux:badge>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content class="px-4 pb-4">
                                    <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Ordering') }}</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $category->ordering }}</span>
                                        </div>

                                        <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $category->description ?: '—' }}
                                        </div>

                                        @canany(['finance.category.update', 'finance.category.delete'])
                                            <div class="flex flex-wrap justify-end gap-2">
                                                @can('finance.category.update')
                                                    <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-category', { categoryId: {{ $category->id }} })">
                                                        {{ __('Edit') }}
                                                    </flux:button>
                                                @endcan
                                                @can('finance.category.delete')
                                                    <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-category', { categoryId: {{ $category->id }} })">
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
                        {{ __('No categories found.') }}
                    </flux:card>
                @endforelse
            </div>
        @endif
    </x-slot:mobile>
</x-app-layout-table>
