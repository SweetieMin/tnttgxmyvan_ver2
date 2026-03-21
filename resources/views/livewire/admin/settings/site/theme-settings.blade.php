<section class="w-full">
    @include('partials.site-settings-heading')

    <x-layouts::settings.site.layout :heading="__('Theme configuration')" :subheading="__('Choose the color preset used across the application interface.')">
        <form wire:submit.prevent="updateThemeSettings()" class="my-6 w-full space-y-6">
            <div class="space-y-6">
                <flux:separator :text="__('Theme preset')" class="my-6" />

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
                    @foreach ($presets as $key => $presetConfig)
                        @php
                            $palette = $key === 'base' ? 'zinc' : $key;
                            $accentShade = $key === 'base' ? '700' : '600';
                        @endphp

                        <button type="button" wire:key="theme-preset-{{ $key }}"
                            wire:click="selectPreset('{{ $key }}')"
                            style="--preset-100: var(--color-{{ $palette }}-100); --preset-200: var(--color-{{ $palette }}-200); --preset-600: var(--color-{{ $palette }}-{{ $accentShade }});"
                            @class([
                                'flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition',
                                'border-[color:var(--preset-600)] bg-[color:var(--preset-100)] ring-2 ring-[color:var(--preset-200)] dark:bg-[color:color-mix(in_oklab,var(--preset-600)_18%,var(--color-zinc-950))]' =>
                                    $preset === $key,
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950/20 dark:hover:border-zinc-600' =>
                                    $preset !== $key,
                            ])>
                            <span
                                class="size-5 rounded-md border shadow-sm"
                                style="background-color: var(--color-{{ $palette }}-500); border-color: var(--color-{{ $palette }}-500);"
                            ></span>
                            <span @class([
                                'font-medium',
                                'text-[color:var(--preset-600)] dark:text-white' => $preset === $key,
                                'text-zinc-900 dark:text-white' => $preset !== $key,
                            ])>
                                {{ __($presetConfig['label']) }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <flux:separator :text="__('Neutral palette')" class="my-6" />

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-5">
                    @foreach ($neutralPalettes as $key => $paletteConfig)
                        @php
                            $neutralVar = in_array($key, ['mauve', 'olive', 'mist', 'taupe'], true)
                                ? "--color-{$key}-400"
                                : "--color-{$key}-400";
                        @endphp

                        <button
                            type="button"
                            wire:key="theme-neutral-palette-{{ $key }}"
                            wire:click="selectNeutralPalette('{{ $key }}')"
                            style="--neutral-selected: var({{ $neutralVar }});"
                            @class([
                                'flex items-center gap-3 rounded-xl border px-4 py-3 text-left transition',
                                'border-[color:var(--neutral-selected)] bg-zinc-50 ring-2 ring-zinc-200 dark:bg-zinc-900/60' => $neutral_palette === $key,
                                'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-950/20 dark:hover:border-zinc-600' => $neutral_palette !== $key,
                            ])
                        >
                            <span
                                class="size-5 rounded-md border shadow-sm"
                                style="background-color: var({{ $neutralVar }}); border-color: var({{ $neutralVar }});"
                            ></span>
                            <span
                                @class([
                                    'font-medium',
                                    'text-zinc-950 dark:text-white' => $neutral_palette === $key,
                                    'text-zinc-900 dark:text-white' => $neutral_palette !== $key,
                                ])
                            >
                                {{ __($paletteConfig['label']) }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <flux:field variant="inline">
                    <flux:switch wire:model.live="seasonal_enabled" :label="__('Enable seasonal theme')"
                        disabled=true />
                    <flux:error name="seasonal_enabled" />
                </flux:field>

                <flux:callout icon="information-circle" color="sky">
                    <flux:callout.heading>{{ __('Theme preview') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('Selecting a preset or neutral palette updates the interface preview immediately. Click Save to store it for the whole system.') }}
                    </flux:callout.text>
                </flux:callout>
            </div>

            <flux:separator class="my-6" />

            <div class="mt-8 flex items-center gap-4">
                @can('settings.site.theme.update')
                    @if ($this->hasThemeChanges())
                        <flux:button variant="primary" type="submit" class="cursor-pointer">
                            {{ __('Save theme settings') }}
                        </flux:button>
                    @endif
                @endcan
            </div>
        </form>
    </x-layouts::settings.layout>
</section>
