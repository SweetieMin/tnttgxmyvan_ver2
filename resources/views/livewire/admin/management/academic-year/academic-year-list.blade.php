<x-app-layout-table :paginator="$academicYears">
    <x-slot:desktop>
        <flux:table :paginate="$academicYears">
            <flux:table.columns>
                <flux:table.column>{{ __('Academic year') }}</flux:table.column>
                <flux:table.column>{{ __('Catechism period') }}</flux:table.column>
                <flux:table.column>{{ __('Catechism average score') }}</flux:table.column>
                <flux:table.column>{{ __('Catechism training score') }}</flux:table.column>
                <flux:table.column>{{ __('Activity period') }}</flux:table.column>
                <flux:table.column>{{ __('Activity score') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>

                @canany(['management.academic-year.update', 'management.academic-year.delete'])
                    <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                @endcanany
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

                        @canany(['management.academic-year.update', 'management.academic-year.delete'])
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
                        @endcanany
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
    </x-slot:desktop>

    <x-slot:mobile>
        @forelse ($academicYears as $academicYear)
            <flux:card class="rounded-2xl p-0" wire:key="academic-year-mobile-{{ $academicYear->id }}">
                <flux:accordion variant="reverse">
                    <flux:accordion.item>
                        <flux:accordion.heading class="px-4 py-3">
                            <div class="flex w-full items-start justify-between gap-3 text-left">
                                <div class="space-y-1">
                                    <div class="font-semibold text-zinc-900 dark:text-white">{{ $academicYear->name }}</div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Catechism period') }}: {{ $academicYear->catechism_period ?: __('N/A') }}
                                    </div>
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('Activity period') }}: {{ $academicYear->activity_period ?: __('N/A') }}
                                    </div>
                                </div>
                                <flux:badge size="sm" :color="$academicYear->status_academic_color">
                                    {{ __($academicYear->status_academic_label) }}
                                </flux:badge>
                            </div>
                        </flux:accordion.heading>

                        <flux:accordion.content class="px-4 pb-4">
                            <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                <div class="grid grid-cols-3 gap-3 rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-900/70">
                                    <div>
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Catechism average score') }}</div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ number_format((float) $academicYear->catechism_avg_score, 2, ',', '.') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Catechism training score') }}</div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ number_format((float) $academicYear->catechism_training_score, 2, ',', '.') }}</div>
                                    </div>
                                    <div>
                                        <div class="text-zinc-500 dark:text-zinc-400">{{ __('Activity score') }}</div>
                                        <div class="font-medium text-zinc-900 dark:text-white">{{ $academicYear->activity_score }}</div>
                                    </div>
                                </div>

                                @canany(['management.academic-year.update', 'management.academic-year.delete'])
                                    <div class="flex flex-wrap justify-end gap-2">
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
                                @endcanany
                            </div>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </flux:card>
        @empty
            <flux:card class="rounded-2xl p-6 text-center">
                {{ __('No academic years found.') }}
            </flux:card>
        @endforelse
    </x-slot:mobile>
</x-app-layout-table>
