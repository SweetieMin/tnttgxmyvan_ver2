<section class="w-full">
    @include('partials.site-settings-heading')

    <x-layouts::settings.layout :heading="__('Theme configuration')" :subheading="__('Choose the color preset used across the application interface.')">
        <form wire:submit.prevent="updateThemeSettings()" class="my-6 w-full space-y-6">
            <div class="space-y-6">
                <flux:separator :text="__('Theme preset')" class="my-6" />

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($presets as $key => $presetConfig)
                        <button
                            type="button"
                            wire:key="theme-preset-{{ $key }}"
                            wire:click="selectPreset('{{ $key }}')"
                            @class([
                                'flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition',
                                'border-accent ring-2 ring-accent/20 bg-white dark:bg-zinc-900/60' => $preset === $key,
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950/20 dark:hover:border-zinc-600' => $preset !== $key,
                            ])
                        >
                            <span class="{{ $presetConfig['dot'] }} size-5 rounded-md border shadow-sm"></span>
                            <span class="font-medium text-zinc-900 dark:text-white">{{ __($presetConfig['label']) }}</span>
                        </button>
                    @endforeach
                </div>

                <flux:field variant="inline">
                    <flux:switch wire:model.live="seasonal_enabled" :label="__('Enable seasonal theme')" />
                    <flux:error name="seasonal_enabled" />
                </flux:field>

                <flux:callout icon="information-circle" color="sky">
                    <flux:callout.heading>{{ __('Theme preview') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('Selecting a preset updates the interface preview immediately. Click Save to store it for the whole system.') }}
                    </flux:callout.text>
                </flux:callout>
            </div>

            <flux:separator class="my-6" />

            <div class="mt-8 flex items-center gap-4">
                @can('settings.site.theme.update')
                    <flux:button variant="primary" type="submit" class="cursor-pointer">
                        {{ __('Save theme settings') }}
                    </flux:button>
                @endcan
            </div>
        </form>
    </x-layouts::settings.layout>
</section>
