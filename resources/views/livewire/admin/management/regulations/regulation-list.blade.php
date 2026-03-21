<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$regulations">
        <flux:table.columns>
            <flux:table.column>{{ __('Ordering') }}</flux:table.column>
            <flux:table.column>{{ __('Description') }}</flux:table.column>
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            <flux:table.column>{{ __('Points') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

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
    </flux:table>
</flux:card>
