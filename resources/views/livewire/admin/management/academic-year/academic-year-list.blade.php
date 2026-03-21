<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$academicYears">
        <flux:table.columns>
            <flux:table.column>{{ __('Academic year') }}</flux:table.column>
            <flux:table.column>{{ __('Catechism period') }}</flux:table.column>
            <flux:table.column>{{ __('Catechism average score') }}</flux:table.column>
            <flux:table.column>{{ __('Catechism training score') }}</flux:table.column>
            <flux:table.column>{{ __('Activity period') }}</flux:table.column>
            <flux:table.column>{{ __('Activity score') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($academicYears as $academicYear)
                <flux:table.row :key="$academicYear->id">
                    <flux:table.cell variant="strong">{{ $academicYear->name }}</flux:table.cell>
                    <flux:table.cell>{{ $academicYear->catechism_period ?: __('N/A') }}</flux:table.cell>
                    <flux:table.cell>{{ number_format((float) $academicYear->catechism_avg_score, 2, ',', '.') }}</flux:table.cell>
                    <flux:table.cell>{{ number_format((float) $academicYear->catechism_training_score, 2, ',', '.') }}</flux:table.cell>
                    <flux:table.cell>{{ $academicYear->activity_period ?: __('N/A') }}</flux:table.cell>
                    <flux:table.cell>{{ $academicYear->activity_score }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$academicYear->status_academic_color">
                            {{ __($academicYear->status_academic_label) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex justify-end gap-2">
                            @can('management.academic-year.update')
                                <flux:button size="sm" variant="ghost" wire:click="$dispatch('edit-academic-year', { academicYearId: {{ $academicYear->id }} })">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan
                            @can('management.academic-year.delete')
                                <flux:button size="sm" variant="danger" wire:click="$dispatch('confirm-delete-academic-year', { academicYearId: {{ $academicYear->id }} })">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="py-8 text-center">
                        {{ __('No academic years found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
