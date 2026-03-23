<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$categories">
        <flux:table.columns>
            <flux:table.column>{{ __('Ordering') }}</flux:table.column>
            <flux:table.column>{{ __('Category') }}</flux:table.column>
            <flux:table.column>{{ __('Description') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

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
    </flux:table>
</flux:card>
