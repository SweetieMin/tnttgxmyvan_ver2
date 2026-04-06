@blaze

@props([
    'title' => null,
    'subTitle' => null,
    'icon' => 'academic-cap',
    'backHref' => null,
    'backLabel' => null,
    'buttonLabel' => null,
    'buttonAction' => null,
    'permission' => null,
    'iconButton' => 'plus'
])

<flux:card class="bg-(--color-background) border-0 rounded-2xl p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- LEFT COLUMN --}}
        <div class="flex flex-col gap-2"> {{-- Chứa cả cụm Icon+Title và cụm Breadcrumbs --}}

            {{-- Hàng 1: Icon + Title/Subtitle --}}
            <div class="flex items-center gap-4">
                <div
                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-(--color-background-icon) text-(--color-accent-content) shrink-0">
                    <flux:icon :name="$icon" class="h-7 w-7" />
                </div>

                <div class="flex flex-col justify-center">
                    <h2 class="text-3xl font-bold leading-tight text-(--color-accent-content)">
                        {{ $title }}
                    </h2>
                    @if ($subTitle)
                        <p class="text-sm font-medium opacity-80 text-(--color-accent-content)">
                            {{ $subTitle }}
                        </p>
                    @endif
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN (Giữ nguyên) --}}

        @if ($backHref || ($buttonLabel && $permission))
            <div class="flex w-full flex-col gap-3 lg:w-auto lg:flex-row lg:items-center lg:justify-end">
                @if ($backHref)
                    <flux:button  icon="arrow-left" :href="$backHref" wire:navigate class="w-full lg:w-auto">
                        {{ $backLabel ?? __('Back') }}
                    </flux:button>
                @endif

                @if ($buttonLabel && $permission)
                    @can($permission)
                        <flux:button icon="{{$iconButton}}" variant="primary" class="w-full lg:w-auto cursor-pointer"
                            wire:click="{{ $buttonAction }}">
                            {{ $buttonLabel }}
                        </flux:button>
                    @endcan
                @endif
            </div>
        @endif

    </div>
</flux:card>
