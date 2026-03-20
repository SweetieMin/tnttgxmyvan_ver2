@blaze

@php $tooltipPosition = $tooltipPosition ??= $attributes->pluck('tooltip:position'); @endphp
@php $tooltipKbd = $tooltipKbd ??= $attributes->pluck('tooltip:kbd'); @endphp
@php $tooltip = $tooltip ??= $attributes->pluck('tooltip'); @endphp

@props([
    'tooltipPosition' => 'right',
    'placeholder' => __('Search...'),
    'tooltipKbd' => null,
    'tooltip' => null,
    'kbd' => null,
    'marquee' => false,
    'variant' => null, // Thêm prop variant
    'color' => null,   // Thêm prop color nếu muốn tùy chỉnh thủ công
])

@php
    // Logic ánh xạ variant sang màu sắc (giống file index.blade.php của callout)
    $mappedColor = match($variant) {
        'success' => 'green',
        'danger' => 'red',
        'warning' => 'yellow',
        'primary' => 'zinc',
        default => $color ?? 'zinc',
    };

    $tooltip = $tooltip ?? $placeholder;
    $tooltipKbd ??= $kbd;

    $tooltipClasses = Flux::classes()
        ->add('w-full')
        ->add('in-data-flux-sidebar-header:in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:hidden')
        ;

    $classes = Flux::classes()
        // Các class nền tảng
        ->add('h-10 py-2 px-3 w-full rounded-lg disabled:shadow-none dark:shadow-none appearance-none text-base sm:text-sm leading-[1.375rem] border relative flex items-center gap-3')
        // Logic đổi màu dựa trên mappedColor
        ->add(match($mappedColor) {
            'green' => 'bg-green-50 border-green-200 text-green-700 dark:bg-green-400/10 dark:border-green-400/20 dark:text-green-400',
            'red' => 'bg-red-50 border-red-200 text-red-700 dark:bg-red-400/10 dark:border-red-400/20 dark:text-red-400',
            'yellow' => 'bg-yellow-50 border-yellow-200 text-yellow-700 dark:bg-yellow-400/10 dark:border-yellow-400/20 dark:text-yellow-400',
            default => 'bg-(--color-background) border-transparent text-(--color-accent-content) ',
        })
        ->add('in-data-flux-sidebar-on-mobile:h-10 in-data-flux-sidebar-collapsed-desktop:px-3')
        ->add('in-data-flux-sidebar-header:in-data-flux-sidebar-collapsed-desktop:in-data-flux-sidebar-active:hidden')
        ;

    // Cập nhật màu cho placeholder và icon dựa trên variant
    $contentClasses = match($mappedColor) {
        'green' => 'text-green-600 dark:text-green-400/60',
        'red' => 'text-red-600 dark:text-red-400/60',
        'yellow' => 'text-yellow-600 dark:text-yellow-400/60',
        default => 'text-(--color-accent-content)',
    };
@endphp

<flux:tooltip :position="$tooltipPosition" :class="$tooltipClasses">
    <button
        {{ $attributes->class($classes) }}
        type="button"
        data-flux-sidebar-search
    >
        <div class="flex items-center justify-center text-xs {{ $contentClasses }} start-0">
            <flux:icon class="size-4" icon="magnifying-glass" variant="outline" />
        </div>

        <div class="in-data-flux-sidebar-collapsed-desktop:hidden block self-center text-start flex-1 font-medium {{ $contentClasses }} overflow-hidden">
            @if ($marquee)
                <marquee behavior="scroll" direction="left" scrollamount="4" class="mt-2">
                    {{ $placeholder }}
                </marquee>
            @else
                {{ $placeholder }}
            @endif
        </div>

        @if ($kbd)
            <div class="in-data-flux-sidebar-collapsed-desktop:hidden top-0 bottom-0 flex items-center justify-center text-xs {{ $contentClasses }} pe-4 end-0">
                {{ $kbd }}
            </div>
        @endif
    </button>

    <flux:tooltip.content :kbd="$tooltipKbd" class="not-in-data-flux-sidebar-collapsed-desktop:hidden cursor-default">
        {{ $tooltip }}
    </flux:tooltip.content>
</flux:tooltip>