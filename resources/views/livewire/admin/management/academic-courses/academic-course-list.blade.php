<x-app-layout-table :paginator="$academicCourses">
    @php($canUpdateAcademicCourse = auth()->user()->can('management.academic-course.update'))

    <x-slot:desktop>
        <flux:table :paginate="$academicCourses">
            <flux:table.columns>
                <flux:table.column>{{ __('Ordering') }}</flux:table.column>
                <flux:table.column>{{ __('Catechism class') }}</flux:table.column>
                <flux:table.column>{{ __('Sector') }}</flux:table.column>
                <flux:table.column>{{ __('Required scores') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            @if ($canUpdateAcademicCourse)
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
                                        <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
                                            {{ __('Options') }}
                                        </flux:button>

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
            @else
                <flux:table.rows>
                    @forelse ($academicCourses as $academicCourse)
                        <flux:table.row :key="$academicCourse->id" wire:key="academic-course-{{ $academicCourse->id }}">
                            <flux:table.cell>
                                <span class="text-sm font-medium">{{ $academicCourse->ordering }}</span>
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
                                        <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
                                            {{ __('Options') }}
                                        </flux:button>

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
            @endif
        </flux:table>
    </x-slot:desktop>

    <x-slot:mobile>
        @if ($canUpdateAcademicCourse)
            <div class="space-y-3">
                @forelse ($academicCourses as $academicCourse)
                    <div wire:key="academic-course-mobile-{{ $academicCourse->id }}">
                        <flux:card class="rounded-2xl p-0">
                            <flux:accordion variant="reverse">
                                <flux:accordion.item>
                                    <flux:accordion.heading class="px-4 py-3">
                                        <div class="flex w-full items-start justify-between gap-3 text-left">
                                            <div class="min-w-0 flex-1 space-y-1">
                                                <div class="font-semibold text-zinc-900 dark:text-white">{{ $academicCourse->course_name }}</div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $academicCourse->sector_name }}</div>
                                            </div>

                                            <div class="shrink-0 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                                {{ $academicCourse->ordering }}
                                            </div>
                                        </div>
                                    </flux:accordion.heading>

                                    <flux:accordion.content class="px-4 pb-4">
                                        <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                            <div class="flex items-center justify-end gap-3">
                                                <flux:badge size="sm" :color="$academicCourse->is_active ? 'emerald' : 'zinc'">
                                                    {{ $academicCourse->is_active ? __('Active') : __('Inactive') }}
                                                </flux:badge>
                                            </div>

                                            <div class="rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-900/70">
                                                <div>{{ __('Catechism average') }}: {{ number_format((float) $academicCourse->catechism_avg_score, 2) }}</div>
                                                <div>{{ __('Catechism training') }}: {{ number_format((float) $academicCourse->catechism_training_score, 2) }}</div>
                                                <div>{{ __('Activity') }}: {{ $academicCourse->activity_score }}</div>
                                            </div>

                                            <div class="flex justify-end">
                                                @canany(['management.academic-course.update', 'management.academic-course.create', 'management.academic-course.delete'])
                                                    <flux:dropdown position="bottom" align="end">
                                                        <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
                                                            {{ __('Options') }}
                                                        </flux:button>

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
                                                @endcanany
                                            </div>
                                        </div>
                                    </flux:accordion.content>
                                </flux:accordion.item>
                            </flux:accordion>
                        </flux:card>
                    </div>
                @empty
                    <flux:card class="rounded-2xl p-6 text-center">
                        {{ __('No catechism - sector classes found.') }}
                    </flux:card>
                @endforelse
            </div>
        @else
            <div class="space-y-3">
                @forelse ($academicCourses as $academicCourse)
                    <flux:card class="rounded-2xl p-0" wire:key="academic-course-mobile-{{ $academicCourse->id }}">
                        <flux:accordion variant="reverse">
                            <flux:accordion.item>
                                <flux:accordion.heading class="px-4 py-3">
                                    <div class="flex w-full items-start justify-between gap-3 text-left">
                                        <div class="min-w-0 flex-1 space-y-1">
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $academicCourse->course_name }}</div>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $academicCourse->sector_name }}</div>
                                        </div>

                                        <div class="shrink-0 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                            {{ $academicCourse->ordering }}
                                        </div>
                                    </div>
                                </flux:accordion.heading>

                                <flux:accordion.content class="px-4 pb-4">
                                    <div class="space-y-4 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                        <div class="flex items-center justify-end gap-3">
                                            <flux:badge size="sm" :color="$academicCourse->is_active ? 'emerald' : 'zinc'">
                                                {{ $academicCourse->is_active ? __('Active') : __('Inactive') }}
                                            </flux:badge>
                                        </div>

                                        <div class="rounded-xl bg-zinc-50 p-3 text-sm dark:bg-zinc-900/70">
                                            <div>{{ __('Catechism average') }}: {{ number_format((float) $academicCourse->catechism_avg_score, 2) }}</div>
                                            <div>{{ __('Catechism training') }}: {{ number_format((float) $academicCourse->catechism_training_score, 2) }}</div>
                                            <div>{{ __('Activity') }}: {{ $academicCourse->activity_score }}</div>
                                        </div>

                                        <div class="flex justify-end">
                                            @canany(['management.academic-course.update', 'management.academic-course.create', 'management.academic-course.delete'])
                                                <flux:dropdown position="bottom" align="end">
                                                    <flux:button size="sm" variant="ghost" icon:trailing="chevron-down">
                                                        {{ __('Options') }}
                                                    </flux:button>

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
                                            @endcanany
                                        </div>
                                    </div>
                                </flux:accordion.content>
                            </flux:accordion.item>
                        </flux:accordion>
                    </flux:card>
                @empty
                    <flux:card class="rounded-2xl p-6 text-center">
                        {{ __('No catechism - sector classes found.') }}
                    </flux:card>
                @endforelse
            </div>
        @endif
    </x-slot:mobile>
</x-app-layout-table>
