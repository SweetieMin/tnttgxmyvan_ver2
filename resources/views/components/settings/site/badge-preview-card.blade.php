@props([
    'reference' => null,
    'interactive' => false,
    'previewSiteFaviconUrl' => '',
    'previewAvatarUrl' => '',
    'previewChristianName' => '',
    'previewFullName' => '',
    'previewQrCodeSvg' => null,
])

<div
    @if ($reference)
        x-ref="{{ $reference }}"
    @endif
    class="relative aspect-[75/110] w-full max-w-md overflow-hidden rounded-[2.2rem] border border-amber-200 bg-[linear-gradient(180deg,#fff9e3_0%,#fff3cb_50%,#ffe9aa_100%)] shadow-sm"
    x-bind:style="`background:${backgroundColor};`"
>
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.75),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.45),transparent_24%)]"></div>
    <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(circle_at_center,_currentColor_0.06rem,_transparent_0.07rem)] [background-size:5.5rem_5.5rem] text-amber-700"></div>

    <template x-for="(block, key) in blocks" :key="key">
        @if ($interactive)
            <button
                type="button"
                class="absolute cursor-move rounded-xl text-[11px] font-medium text-amber-900 transition focus:outline-none"
                x-bind:class="(key !== 'avatar' && (draggingKey === key || selectedKey === key)) ? 'ring-2 ring-amber-500' : ''"
                x-bind:style="styleFor(key)"
                x-on:click.prevent="selectBlock(key)"
                x-on:pointerdown="beginDrag(key, $event)"
            >
                @include('components.settings.site.partials.badge-preview-blocks')
            </button>
        @else
            <div class="absolute rounded-xl bg-transparent text-amber-900" x-bind:style="styleFor(key)">
                @include('components.settings.site.partials.badge-preview-blocks')
            </div>
        @endif
    </template>
</div>
