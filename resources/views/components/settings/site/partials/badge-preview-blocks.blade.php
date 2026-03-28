<template x-if="key === 'logo'">
    <div class="flex size-full items-center justify-center">
        <div class="aspect-square h-full max-h-full max-w-full overflow-hidden rounded-full">
            <img src="{{ $previewSiteFaviconUrl }}" alt="{{ __('Favicon') }}" class="size-full object-contain">
        </div>
    </div>
</template>

<template x-if="key === 'heading'">
    <div class="flex size-full flex-col items-center justify-center gap-1 bg-transparent px-[4%] text-center leading-tight text-[#c79f57]">
        <span class="block max-w-full whitespace-nowrap text-[1.1rem] font-semibold tracking-tight" x-text="title || '{{ __('Badge title') }}'"></span>
        <span class="block max-w-full whitespace-nowrap text-[1.1rem] font-semibold tracking-tight" x-text="subtitle || '{{ __('Badge subtitle') }}'"></span>
    </div>
</template>

<template x-if="key === 'qr'">
    <div class="flex size-full items-center justify-center">
        <div class="flex aspect-square h-full max-h-full max-w-full items-center justify-center bg-white p-[2%] shadow-sm">
            @if ($previewQrCodeSvg)
                <div class="size-full [&_svg]:size-full">
                    {!! $previewQrCodeSvg !!}
                </div>
            @else
                <div class="flex size-full items-center justify-center rounded-sm border border-zinc-200/40 bg-white text-center text-[10px]">
                    QR
                </div>
            @endif
        </div>
    </div>
</template>

<template x-if="key === 'name_panel'">
    <div class="size-full rounded-[1.35rem]" x-bind:style="`background:${namePanelColor}; opacity:0.55;`"></div>
</template>

<template x-if="key === 'avatar'">
    <div class="flex size-full items-center justify-center">
        <div class="flex aspect-square h-full max-h-full max-w-full items-center justify-center overflow-hidden rounded-full bg-white  shadow-md">
            <div class="flex size-full items-center justify-center overflow-hidden rounded-full bg-white">
                <img src="{{ $previewAvatarUrl }}" alt="{{ __('Avatar') }}" class="size-full object-cover">
            </div>
        </div>
    </div>
</template>

<template x-if="key === 'christian_name'">
    <div class="flex size-full items-end justify-center bg-transparent pb-[4%] text-center text-[1.35rem] font-semibold uppercase leading-tight tracking-wide text-[#c5a061]">
        {{ $previewChristianName }}
    </div>
</template>

<template x-if="key === 'full_name'">
    <div class="flex size-full items-start justify-center bg-transparent px-2 pt-[2%] text-center text-[1.50rem] font-bold uppercase leading-tight text-[#c5a061]">
        {{ $previewFullName }}
    </div>
</template>
