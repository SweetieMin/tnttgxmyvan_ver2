<section class="w-full">
    @include('partials.site-settings-heading')

    <x-layouts::settings.site.layout :heading="__('Maintenance configuration')" :subheading="__('Control maintenance mode, access secret, and the maintenance notice shown to users.')">

        <form wire:submit.prevent="updateMaintenanceSettings()" class="my-6 w-full">
            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-8 items-start">

                <div class="space-y-6">
                    <flux:separator :text="__('Maintenance mode')" class="my-6" />

                    <flux:field variant="inline">
                        <flux:switch wire:model.live="is_maintenance" :label="__('Enable maintenance mode')" />

                        <flux:error name="is_maintenance" />
                    </flux:field>

                    @if ($is_maintenance)
                        <flux:input icon="key" readonly :copyable="$is_maintenance" wire:model.live="secret_key"
                            :label="__('Secret key')" />

                        <flux:textarea wire:model.live.debounce.500ms="message" :label="__('Maintenance message')"
                            :placeholder="__('Please provide a maintenance notice')" class="min-h-30" />
                    @endif
                </div>

                <flux:separator vertical class="hidden md:block" />

                <div class="space-y-6">
                    @if ($is_maintenance)
                        <flux:separator :text="__('Maintenance schedule')" class="my-6" />

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:mt-17">
                            <flux:time-picker wire:model.live="start_at" :label="__('Start at')" type="input" locale="vi-VN" />
                            <flux:time-picker wire:model.live="end_at" :label="__('End at')" type="input" locale="vi-VN" />
                        </div>

                        <flux:callout icon="information-circle" color="sky" class="md:mt-12">
                            <flux:callout.heading>{{ __('Maintenance access') }}</flux:callout.heading>
                            <flux:callout.text>
                                {{ __('Keep the secret key private. It can be shared with testers or administrators who still need temporary access during maintenance.') }}
                            </flux:callout.text>
                        </flux:callout>
                    @endif
                </div>

            </div>

            <flux:separator class="my-6" />
            <div class="mt-8 flex items-center gap-4">
                @can('settings.site.maintenance.update')
                    @if ($this->hasMaintenanceChanges())
                        <flux:button variant="primary" type="submit" class="cursor-pointer">
                            {{ $app_is_in_maintenance ? __('Disable maintenance mode') : __('Enable maintenance mode') }}
                        </flux:button>
                    @endif
                @endcan
            </div>

        </form>

    </x-layouts::settings.layout>

    <flux:modal name="maintenance-confirm" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Enable maintenance mode?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('The system will switch to maintenance mode and users will not be able to access it.') }}<br>
                    {{ __('Are you sure you want to continue?') }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                @can('settings.site.maintenance.update')
                    <flux:button variant="danger" wire:click="enableMaintenanceConfirm">
                        {{ __('Confirm') }}
                    </flux:button>
                @endcan
            </div>
        </div>
    </flux:modal>

</section>
