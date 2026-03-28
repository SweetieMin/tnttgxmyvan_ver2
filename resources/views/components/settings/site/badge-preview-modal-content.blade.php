@props([
    'blocks' => [],
    'backgroundColor' => '#fff3cb',
    'namePanelColor' => '#efd089',
    'title' => '',
    'subtitle' => '',
    'useParentState' => false,
    'previewSiteFaviconUrl' => '',
    'previewAvatarUrl' => '',
    'previewChristianName' => '',
    'previewFullName' => '',
    'previewQrCodeSvg' => null,
    'description' => __('Review the current badge layout with the configured title and subtitle before saving.'),
    'exportAction' => 'exportBadgePreviewPng',
    'closeAction' => null,
])

<div class="space-y-5">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('Badge preview') }}</flux:heading>
        <flux:text>{{ $description }}</flux:text>
    </div>

    @if ($useParentState)
        <div class="flex justify-center">
            <x-settings.site.badge-preview-card
                :preview-site-favicon-url="$previewSiteFaviconUrl"
                :preview-avatar-url="$previewAvatarUrl"
                :preview-christian-name="$previewChristianName"
                :preview-full-name="$previewFullName"
                :preview-qr-code-svg="$previewQrCodeSvg"
            />
        </div>
    @else
        <div
            x-data="{
                blocks: @js($blocks),
                title: @js($title),
                subtitle: @js($subtitle),
                backgroundColor: @js($backgroundColor),
                namePanelColor: @js($namePanelColor),
                styleFor(key) {
                    const block = this.blocks[key];

                    return `left:${block.x}%;top:${block.y}%;width:${block.w}%;height:${block.h}%;`;
                },
            }"
            class="flex justify-center"
        >
            <x-settings.site.badge-preview-card
                :preview-site-favicon-url="$previewSiteFaviconUrl"
                :preview-avatar-url="$previewAvatarUrl"
                :preview-christian-name="$previewChristianName"
                :preview-full-name="$previewFullName"
                :preview-qr-code-svg="$previewQrCodeSvg"
            />
        </div>
    @endif

    <div class="flex items-center justify-end gap-2">
        <flux:button type="button" variant="ghost" wire:click="{{ $exportAction }}">
            {{ __('Export PNG') }}
        </flux:button>

        @if ($closeAction)
            <flux:button variant="primary" wire:click="{{ $closeAction }}">
                {{ __('Close') }}
            </flux:button>
        @else
            <flux:modal.close>
                <flux:button variant="primary">{{ __('Close') }}</flux:button>
            </flux:modal.close>
        @endif
    </div>
</div>
