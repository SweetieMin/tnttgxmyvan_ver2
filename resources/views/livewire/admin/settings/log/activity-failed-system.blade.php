<section class="w-full">
    @include('partials.site-settings-log-heading')

    <x-layouts::settings.log.layout :heading="__('Failed activity logs')" :subheading="__('Review failed database actions and persistence errors')">

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
                                    <flux:table.column>{{ __('Action') }}</flux:table.column>
                                    <flux:table.column>{{ __('Subject') }}</flux:table.column>
                                    <flux:table.column align="center">{{ __('Details') }}</flux:table.column>
                                </flux:table.columns>

                                <flux:table.rows>
                                    @forelse ($this->activityFailedLogs as $activityFailedLog)
                                        <flux:table.row wire:key="activity-failed-row-{{ $activityFailedLog->id }}">
                                            <flux:table.cell>
                                                <div class="flex flex-col leading-tight">
                                                    <span class="font-medium">{{ $activityFailedLog->created_at?->format('d/m/Y') ?? '-' }}</span>
                                                    <span class="text-xs text-zinc-500">{{ $activityFailedLog->created_at?->format('H:i:s') ?? '-' }}</span>
                                                </div>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                {{ data_get($activityFailedLog->causer, 'full_name') ?? data_get($activityFailedLog->causer, 'name') ?? __('System') }}
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                <flux:badge size="sm" color="rose">
                                                    {{ $activityFailedLog->action ?: __('N/A') }}
                                                </flux:badge>
                                            </flux:table.cell>
                                            <flux:table.cell>
                                                {{ class_basename((string) $activityFailedLog->subject_type) ?: __('N/A') }}
                                                @if ($activityFailedLog->subject_id)
                                                    #{{ $activityFailedLog->subject_id }}
                                                @endif
                                            </flux:table.cell>
                                            <flux:table.cell align="center">
                                                <flux:link as="button" wire:click="openDetail({{ $activityFailedLog->id }})" class="cursor-pointer">
                                                    {{ __('View') }}
                                                </flux:link>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @empty
                                        <flux:table.row>
                                            <flux:table.cell colspan="5" class="py-8 text-center text-zinc-500">
                                                {{ __('No failed activity logs found.') }}
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforelse
                                </flux:table.rows>
                            </flux:table>
                        </flux:card>
                    </div>

                    <div class="space-y-3 md:hidden">
                        <div class="space-y-3">
                            @forelse ($this->activityFailedLogs as $activityFailedLog)
                                <flux:card class="p-0" wire:key="activity-failed-mobile-{{ $activityFailedLog->id }}">
                                    <flux:accordion variant="reverse">
                                        <flux:accordion.item>
                                            <flux:accordion.heading class="px-3 py-2.5">
                                                <div class="flex w-full items-start justify-between gap-3">
                                                    <div class="space-y-0.5 text-left">
                                                        <p class="text-sm font-semibold">
                                                            {{ $activityFailedLog->created_at?->format('d/m/Y') ?? '-' }}
                                                            <span class="text-xs text-zinc-500">{{ $activityFailedLog->created_at?->format('H:i:s') ?? '-' }}</span>
                                                        </p>
                                                        <p class="text-xs text-zinc-500">
                                                            {{ data_get($activityFailedLog->causer, 'full_name') ?? data_get($activityFailedLog->causer, 'name') ?? __('System') }}
                                                        </p>
                                                    </div>
                                                    <flux:badge size="sm" color="rose">
                                                        {{ $activityFailedLog->action ?: __('N/A') }}
                                                    </flux:badge>
                                                </div>
                                            </flux:accordion.heading>

                                            <flux:accordion.content class="px-3 pb-3">
                                                <div class="space-y-1 border-t border-zinc-100 pt-2 text-sm">
                                                    <p>
                                                        <span class="font-semibold">{{ __('Subject') }}:</span>
                                                        {{ class_basename((string) $activityFailedLog->subject_type) ?: __('N/A') }}
                                                        @if ($activityFailedLog->subject_id)
                                                            #{{ $activityFailedLog->subject_id }}
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <span class="font-semibold">{{ __('Message') }}:</span>
                                                        {{ \Illuminate\Support\Str::limit($activityFailedLog->message, 120) }}
                                                    </p>
                                                </div>

                                                <div class="mt-3 flex justify-end">
                                                    <flux:link as="button" wire:click="openDetail({{ $activityFailedLog->id }})" class="cursor-pointer">
                                                        {{ __('View') }}
                                                    </flux:link>
                                                </div>
                                            </flux:accordion.content>
                                        </flux:accordion.item>
                                    </flux:accordion>
                                </flux:card>
                            @empty
                                <flux:card class="p-6 text-center text-zinc-500">
                                    {{ __('No failed activity logs found.') }}
                                </flux:card>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div>
                    {{ $this->activityFailedLogs->links() }}
                </div>
            @else
                <div class="space-y-4">
                    <div class="hidden md:block">
                        <flux:card>
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>{{ __('Time') }}</flux:table.column>
                                    <flux:table.column>{{ __('User') }}</flux:table.column>
                                    <flux:table.column>{{ __('Action') }}</flux:table.column>
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
                                <flux:card class="space-y-3" wire:key="activity-failed-mobile-skeleton-{{ $i }}">
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

        <flux:modal name="activity-failed-detail" class="md:w-200">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ __('Failed activity log details') }}</flux:heading>
                    <flux:subheading>
                        #{{ $selectedActivityFailedLog['id'] ?? '-' }} -
                        {{ $selectedActivityFailedLog['created_at'] ?? '-' }}
                    </flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <flux:card>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-semibold">{{ __('Log name') }}:</span>
                                {{ $selectedActivityFailedLog['log_name'] ?? '-' }}</p>
                            <p><span class="font-semibold">{{ __('Action') }}:</span>
                                {{ $selectedActivityFailedLog['action'] ?? '-' }}</p>
                            <p><span class="font-semibold">{{ __('Message') }}:</span>
                                {{ $selectedActivityFailedLog['message'] ?? '-' }}</p>
                            <p><span class="font-semibold">{{ __('Exception') }}:</span>
                                {{ $selectedActivityFailedLog['exception'] ?? __('N/A') }}</p>
                        </div>
                    </flux:card>

                    <flux:card>
                        <div class="space-y-2 text-sm">
                            <p>
                                <span class="font-semibold">{{ __('Subject') }}:</span>
                                {{ class_basename((string) ($selectedActivityFailedLog['subject_type'] ?? '')) ?: __('N/A') }}
                                @if (!empty($selectedActivityFailedLog['subject_id']))
                                    #{{ $selectedActivityFailedLog['subject_id'] }}
                                @endif
                            </p>
                            <p><span class="font-semibold">{{ __('Causer') }}:</span>
                                {{ $selectedActivityFailedLog['causer_name'] ?? __('System') }}</p>
                            <p><span class="font-semibold">{{ __('Causer id') }}:</span>
                                {{ $selectedActivityFailedLog['causer_id'] ?? __('N/A') }}</p>
                            <p><span class="font-semibold">{{ __('Updated') }}:</span>
                                {{ $selectedActivityFailedLog['updated_at'] ?? __('N/A') }}</p>
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
