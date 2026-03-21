<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$programs">
        <flux:table.columns>
            <flux:table.column>{{ __('Ordering') }}</flux:table.column>
            <flux:table.column>{{ __('Catechism class') }}</flux:table.column>
            <flux:table.column>{{ __('Sector') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

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
    </flux:table>
</flux:card>
