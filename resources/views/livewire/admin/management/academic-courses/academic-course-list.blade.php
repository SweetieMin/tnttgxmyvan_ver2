<flux:card class="overflow-hidden rounded-2xl p-6">
    <flux:table :paginate="$academicCourses">
        <flux:table.columns>
            <flux:table.column>{{ __('Ordering') }}</flux:table.column>
            <flux:table.column>{{ __('Catechism class') }}</flux:table.column>
            <flux:table.column>{{ __('Sector') }}</flux:table.column>
            <flux:table.column>{{ __('Required scores') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows wire:sort="sortAcademicCourse">
            @forelse ($academicCourses as $academicCourse)
                <flux:table.row :key="$academicCourse->id" wire:key="academic-course-{{ $academicCourse->id }}" wire:sort:item="{{ $academicCourse->id }}">
                    <flux:table.cell>
                        <flux:button size="sm" variant="ghost" wire:sort:handle class="cursor-grab active:cursor-grabbing">
                            {{ $academicCourse->ordering }}
                        </flux:button>
                    </flux:table.cell>
                    <flux:table.cell variant="strong">{{ $academicCourse->course_name }}</flux:table.cell>
                    <flux:table.cell>{{ $academicCourse->sector_name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="space-y-1 text-sm">
                            <div>{{ __('Catechism average') }}: {{ number_format((float) $academicCourse->catechism_avg_score, 2) }}</div>
                            <div>{{ __('Catechism training') }}: {{ number_format((float) $academicCourse->catechism_training_score, 2) }}</div>
                            <div>{{ __('Activity') }}: {{ $academicCourse->activity_score }}</div>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :color="$academicCourse->is_active ? 'emerald' : 'zinc'">
                            {{ $academicCourse->is_active ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        @canany(['management.academic-course.update', 'management.academic-course.create', 'management.academic-course.delete'])
                            <flux:dropdown position="bottom" align="end">

                                <flux:button icon="ellipsis-horizontal" />

                                <flux:menu>
                                    @can('management.academic-course.update')
                                        <flux:menu.item icon="pencil-square" wire:click="$dispatch('edit-academic-course', { academicCourseId: {{ $academicCourse->id }} })">
                                            {{ __('Edit') }}
                                        </flux:menu.item>
                                    @endcan

                                    @can('management.academic-course.create')
                                        <flux:menu.item icon="document-duplicate" wire:click="$dispatch('duplicate-academic-course', { academicCourseId: {{ $academicCourse->id }} })">
                                            {{ __('Duplicate') }}
                                        </flux:menu.item>
                                    @endcan

                                    @can('management.academic-course.delete')
                                        <flux:menu.separator />

                                        <flux:menu.item variant="danger" icon="trash" wire:click="$dispatch('confirm-delete-academic-course', { academicCourseId: {{ $academicCourse->id }} })">
                                            {{ __('Delete') }}
                                        </flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        @else
                            <span class="text-sm text-zinc-400">—</span>
                        @endcanany
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="py-8 text-center">
                        {{ __('No catechism - sector classes found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
