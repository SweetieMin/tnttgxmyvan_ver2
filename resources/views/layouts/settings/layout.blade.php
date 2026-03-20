<div class="flex items-start max-md:flex-col">

    <div class="me-4 w-full pb-4 md:w-sm">
        <flux:card class="space-y-6">
            <flux:navlist wire:ignore>
                @can('settings.site.general.view')
                    <flux:navlist.item :href="route('admin.settings.site.general')"
                        :current="request()->routeIs('admin.settings.site.general')" wire:navigate>
                        {{ __('General configuration') }}
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
            <div class="mt-5 w-full  px-2">
                {{ $slot }}
            </div>
        </flux:card>
    </div>
</div>
