<div class="flex items-start max-md:flex-col">

    <div class="me-4 w-full pb-4 md:w-sm">
        <flux:card class="space-y-6">
            <flux:navlist wire:ignore>
                @can('settings.log.activity.view')
                    <flux:navlist.item :href="route('admin.settings.log.activity')"
                        :current="request()->routeIs('admin.settings.log.activity')" wire:navigate>
                        {{ __('Activity system') }}
                    </flux:navlist.item>
                @endcan

                @can('settings.log.activity-failed.view')
                    <flux:navlist.item :href="route('admin.settings.log.activity-failed')"
                        :current="request()->routeIs('admin.settings.log.activity-failed')" wire:navigate>
                        {{ __('Failed activity logs') }}
                    </flux:navlist.item>
                @endcan

            </flux:navlist>
        </flux:card>
    </div>

    <flux:separator class="md:hidden" />

    <div class="self-stretch max-md:pt-6 w-full">
        <flux:card class="space-y-6 ">
            <flux:heading>{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
            <flux:separator class="my-2" />
            <div class="mt-2 w-full  px-2">
                {{ $slot }}
            </div>
        </flux:card>
    </div>
</div>
