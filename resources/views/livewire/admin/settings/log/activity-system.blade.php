<section class="w-full">
    @include('partials.site-settings-log-heading')

    <x-layouts::settings.log.layout :heading="__('Activity system')" :subheading="__('Manage activity logs and system events')">

        <x-app-filter :has-pages="true" />

        <div class="space-y-4" wire:init="loadActivities">
            @if ($readyToLoad)
                <div class="space-y-4 mt-2">
                    <div class="hidden md:block">
                        <flux:card>
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Time') }}</flux:table.column>
                                    <flux:table.column>{{ __('User') }}</flux:table.column>
                                    <flux:table.column>{{ __('Event') }}</flux:table.column>
                                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                                    <flux:table.column align="center">{{ __('Details') }}</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    @forelse ($this->activities as $activity)
                                        <flux:table.row wire:key="activity-row-{{ $activity->id }}">
                                            <flux:table.cell>
                                                <div class="flex flex-col leading-tight">
                                                    <span class="font-medium">{{ $activity->created_at?->format('d/m/Y') ?? '-' }}</span>
                                                    <span class="text-xs text-zinc-500">{{ $activity->created_at?->format('H:i:s') ?? '-' }}</span>
                                                </div>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                {{ data_get($activity->causer, 'full_name') ?? data_get($activity->causer, 'name') ?? __('System') }}
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <flux:badge size="sm" color="indigo">
                                                    {{ $activity->event ?? __('N/A') }}
                                                </flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                {{ class_basename((string) $activity->subject_type) ?: __('N/A') }}
                                                @if ($activity->subject_id)
                                                    #{{ $activity->subject_id }}
                                                @endif
                                            </flux:table.cell>
                                            <flux:table.cell align="center">
                                                <flux:link as="button" wire:click="openDetail({{ $activity->id }})" class="cursor-pointer">
                                                    {{ __('View') }}
                                                </flux:link>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @empty
                                        <flux:table.row>
                                            <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                                                {{ __('No activity logs found.') }}
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforelse
                                </flux:table.rows>
                            </flux:table>
                        </flux:card>
                    </div>

                    <div class="space-y-3 md:hidden">
                        <div class="space-y-3">
                            @forelse ($this->activities as $activity)
                                <flux:card class="p-0" wire:key="activity-mobile-{{ $activity->id }}">
                                    <flux:accordion variant="reverse">
                                        <flux:accordion.item>
                                            <flux:accordion.heading class="px-3 py-2.5">
                                                <div class="flex w-full items-start justify-between gap-3">
                                                    <div class="space-y-0.5 text-left">
                                                        <p class="text-sm font-semibold">
                                                            {{ $activity->created_at?->format('d/m/Y') ?? '-' }}
                                                            <span class="text-xs text-zinc-500">{{ $activity->created_at?->format('H:i:s') ?? '-' }}</span>
                                                        </p>
                                                        <p class="text-xs text-zinc-500">
                                                            {{ data_get($activity->causer, 'full_name') ?? data_get($activity->causer, 'name') ?? __('System') }}
                                                        </p>
                                                    </div>
                                                    <flux:badge size="sm" color="indigo">
                                                        {{ $activity->event ?? __('N/A') }}
                                                    </flux:badge>
                                                </div>
                                            </flux:accordion.heading>

                                            <flux:accordion.content class="px-3 pb-3">
                                                <div class="space-y-1 border-t border-zinc-100 pt-2 text-sm">
                                                    <p>
                                                        <span class="font-semibold">{{ __('Subject') }}:</span>
                                                        {{ class_basename((string) $activity->subject_type) ?: __('N/A') }}
                                                        @if ($activity->subject_id)
                                                            #{{ $activity->subject_id }}
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <span class="font-semibold">{{ __('Description') }}:</span>
                                                        {{ \Illuminate\Support\Str::limit($activity->description, 120) }}
                                                    </p>
                                                </div>

                                                <div class="mt-3 flex justify-end">
                                                    <flux:link as="button" wire:click="openDetail({{ $activity->id }})" class="cursor-pointer">
                                                        {{ __('View') }}
                                                    </flux:link>
                                                </div>
                                            </flux:accordion.content>
                                        </flux:accordion.item>
                                    </flux:accordion>
                                </flux:card>
                            @empty
                                <flux:card class="p-6 text-center text-zinc-500">
                                    {{ __('No activity logs found.') }}
                                </flux:card>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div>
                    {{ $this->activities->links() }}
                </div>
            @else
                <div class="space-y-4">
                    <div class="hidden md:block">
                        <flux:card>
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Time') }}</flux:table.column>
                                    <flux:table.column>{{ __('User') }}</flux:table.column>
                                    <flux:table.column>{{ __('Event') }}</flux:table.column>
                                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                                    <flux:table.column align="center">{{ __('Details') }}</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    <flux:table.row>
                                        <flux:table.cell colspan="5" class="p-0">
                                            <x-placeholder.table />
                                        </flux:table.cell>
                                    </flux:table.row>
                                </flux:table.rows>
                            </flux:table>
                        </flux:card>
                    </div>

                    <div class="space-y-3 md:hidden">
                        <div class="space-y-3">
                            @for ($i = 0; $i < 3; $i++)
                                <flux:card class="space-y-3" wire:key="activity-mobile-skeleton-{{ $i }}">
                                    <div class="space-y-2">
                                        <flux:skeleton class="h-4 w-24" />
                                        <flux:skeleton class="h-3 w-16" />
                                    </div>
                                    <flux:skeleton class="h-4 w-40" />
                                    <flux:skeleton class="h-4 w-32" />
                                    <div class="flex justify-end">
                                        <flux:skeleton class="h-4 w-12" />
                                    </div>
                                </flux:card>
                            @endfor
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <flux:modal name="activity-detail" class="md:w-200">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ __('Activity log details') }}</flux:heading>
                    <flux:subheading>
                        #{{ $selectedActivity['id'] ?? '-' }} -
                        {{ $selectedActivity['created_at'] ?? '-' }}
                    </flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <flux:card>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold">{{ __('Log name') }}:</span>
                                {{ $selectedActivity['log_name'] ?? '-' }}</p>
                            <p><span class="font-semibold">{{ __('Description') }}:</span>
                                {{ $selectedActivity['description'] ?? '-' }}</p>
                            <p>
                                <span class="font-semibold">{{ __('Subject') }}:</span>
                                {{ class_basename((string) ($selectedActivity['subject_type'] ?? '')) ?: __('N/A') }}
                                @if (!empty($selectedActivity['subject_id']))
                                    #{{ $selectedActivity['subject_id'] }}
                                @endif
                            </p>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold">{{ __('Causer') }}:</span>
                                {{ $selectedActivity['causer_name'] ?? __('System') }}</p>
                            <p><span class="font-semibold">{{ __('Causer id') }}:</span>
                                {{ $selectedActivity['causer_id'] ?? __('N/A') }}</p>
                            <p><span class="font-semibold">{{ __('Updated') }}:</span>
                                {{ $selectedActivity['updated_at'] ?? __('N/A') }}</p>
                        </div>
                    </flux:card>
                </div>

                <flux:card>
                    <flux:heading size="sm">{{ __('Properties') }}</flux:heading>
                    <pre class="mt-2 max-h-96 overflow-auto rounded-lg bg-zinc-900 p-3 text-xs text-zinc-100">{{ $selectedProperties }}</pre>
                </flux:card>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="primary">{{ __('Close') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    </x-layouts::settings.log.layout>
</section>
