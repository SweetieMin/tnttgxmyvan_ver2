@props([
    'blocks' => [],
    'backgroundColor' => '#fff3cb',
    'namePanelColor' => '#efd089',
    'title' => '',
    'subtitle' => '',
    'previewSiteFaviconUrl' => '',
    'previewAvatarUrl' => '',
    'previewChristianName' => '',
    'previewFullName' => '',
    'previewQrCodeSvg' => null,
    'canUpdate' => false,
    'hasChanges' => false,
])

<div
    x-data="{
        title: $wire.entangle('badge_title').live,
        subtitle: $wire.entangle('badge_subtitle').live,
        layoutJson: $wire.entangle('badge_layout').live,
        backgroundColor: $wire.entangle('badge_background_color').live,
        namePanelColor: $wire.entangle('badge_name_panel_color').live,
        blocks: @js($blocks),
        selectedKey: 'heading',
        linkedResize: true,
        blockLabels: {
            logo: '{{ __('Favicon') }}',
            heading: '{{ __('Heading') }}',
            qr: 'QR',
            name_panel: '{{ __('Name panel') }}',
            avatar: '{{ __('Avatar') }}',
            christian_name: '{{ __('Christian name') }}',
            full_name: '{{ __('Full name') }}',
        },
        draggingKey: null,
        dragOffsetX: 0,
        dragOffsetY: 0,
        beginDrag(key, event) {
            const blockRect = event.currentTarget.getBoundingClientRect();

            this.selectedKey = key;
            this.draggingKey = key;
            this.dragOffsetX = event.clientX - blockRect.left;
            this.dragOffsetY = event.clientY - blockRect.top;
            event.preventDefault();
        },
        drag(event) {
            if (! this.draggingKey) {
                return;
            }

            const canvasRect = this.$refs.canvas.getBoundingClientRect();
            const block = this.blocks[this.draggingKey];
            const nextX = ((event.clientX - canvasRect.left - this.dragOffsetX) / canvasRect.width) * 100;
            const nextY = ((event.clientY - canvasRect.top - this.dragOffsetY) / canvasRect.height) * 100;

            block.x = Math.max(0, Math.min(100 - block.w, Math.round(nextX)));
            block.y = Math.max(0, Math.min(100 - block.h, Math.round(nextY)));

            this.syncLayout();
        },
        stopDrag() {
            this.draggingKey = null;
        },
        syncLayout() {
            this.layoutJson = JSON.stringify(this.blocks);
        },
        resetLayout() {
            this.blocks = @js($blocks);
            this.selectedKey = 'heading';
            this.syncLayout();
        },
        selectBlock(key) {
            this.selectedKey = key;
        },
        styleFor(key) {
            const block = this.blocks[key];

            return `left:${block.x}%;top:${block.y}%;width:${block.w}%;height:${block.h}%;`;
        },
        dimensionValue(axis) {
            return this.blocks[this.selectedKey]?.[axis] ?? 0;
        },
        setSelectedDimension(axis, value) {
            const block = this.blocks[this.selectedKey];

            if (! block) {
                return;
            }

            const normalized = Math.max(6, Math.min(100, Math.round(Number(value) || 0)));
            const previousWidth = block.w;
            const previousHeight = block.h;

            if (axis === 'w') {
                block.w = Math.min(normalized, 100 - block.x);

                if (this.linkedResize && previousWidth > 0) {
                    block.h = Math.min(
                        Math.max(6, Math.round((block.w / previousWidth) * previousHeight)),
                        100 - block.y,
                    );
                }
            } else {
                block.h = Math.min(normalized, 100 - block.y);

                if (this.linkedResize && previousHeight > 0) {
                    block.w = Math.min(
                        Math.max(6, Math.round((block.h / previousHeight) * previousWidth)),
                        100 - block.x,
                    );
                }
            }

            this.syncLayout();
        },
    }"
    x-on:pointermove.window="drag($event)"
    x-on:pointerup.window="stopDrag()"
    x-on:pointercancel.window="stopDrag()"
    class="space-y-6"
>
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="space-y-4">
            <flux:separator :text="__('Badge template preview')" class="my-2" />

            <x-settings.site.badge-preview-card
                reference="canvas"
                interactive
                :preview-site-favicon-url="$previewSiteFaviconUrl"
                :preview-avatar-url="$previewAvatarUrl"
                :preview-christian-name="$previewChristianName"
                :preview-full-name="$previewFullName"
                :preview-qr-code-svg="$previewQrCodeSvg"
            />
        </div>

        <form wire:submit.prevent="updateBadgeTemplateSettings()" class="space-y-4">
            <flux:separator :text="__('Badge card configuration')" class="my-2" />

            <div class="grid grid-cols-1 gap-4">
                <div class="col-span-1">
                    <flux:input
                        wire:model.live.debounce.500ms="badge_title"
                        :label="__('Badge title')"
                        type="text"
                        :placeholder="__('Doan TNTT Giao Xu My Van')"
                    />
                </div>

                <div class="col-span-1">
                    <flux:input
                        wire:model.live.debounce.500ms="badge_subtitle"
                        :label="__('Badge subtitle')"
                        type="text"
                        :placeholder="__('Xu Doan Giuse Vien')"
                    />
                </div>

                <div class="col-span-1">
                    <flux:input
                        wire:model.live="badge_background_color"
                        :label="__('Badge background color')"
                        type="color"
                    />
                </div>

                <div class="col-span-1">
                    <flux:input
                        wire:model.live="badge_name_panel_color"
                        :label="__('Name panel background color')"
                        type="color"
                    />
                </div>
            </div>

            <input type="hidden" wire:model="badge_layout">
            <flux:error name="badge_layout" />


            <div class="space-y-4 rounded-2xl border border-zinc-200 p-4 dark:border-white/10">
                <flux:heading size="sm">{{ __('Resize selected block') }}</flux:heading>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Block') }}</label>
                    <select
                        x-model="selectedKey"
                        class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-white/10 dark:bg-zinc-900 dark:text-white"
                    >
                        <template x-for="(label, key) in blockLabels" :key="`selector-${key}`">
                            <option :value="key" x-text="label"></option>
                        </template>
                    </select>
                </div>

                <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 px-3 py-2 dark:border-white/10">
                    <div class="text-sm font-medium text-zinc-700 dark:text-zinc-200">{{ __('Link width and height') }}</div>
                    <flux:button
                        type="button"
                        variant="ghost"
                        size="sm"
                        x-bind:icon="linkedResize ? 'link' : 'link-slash'"
                        x-bind:class="linkedResize ? 'text-emerald-600' : 'text-zinc-500'"
                        x-on:click="linkedResize = ! linkedResize"
                    >
                        <span x-text="linkedResize ? '{{ __('Linked') }}' : '{{ __('Unlinked') }}'"></span>
                    </flux:button>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <label class="font-medium text-zinc-700 dark:text-zinc-200">{{ __('Width') }}</label>
                        <span class="text-zinc-500" x-text="`${dimensionValue('w')}%`"></span>
                    </div>
                    <input
                        type="range"
                        min="6"
                        max="100"
                        :value="dimensionValue('w')"
                        x-on:input="setSelectedDimension('w', $event.target.value)"
                        class="w-full"
                    >
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <label class="font-medium text-zinc-700 dark:text-zinc-200">{{ __('Height') }}</label>
                        <span class="text-zinc-500" x-text="`${dimensionValue('h')}%`"></span>
                    </div>
                    <input
                        type="range"
                        min="6"
                        max="100"
                        :value="dimensionValue('h')"
                        x-on:input="setSelectedDimension('h', $event.target.value)"
                        class="w-full"
                    >
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:modal.trigger name="badge-template-preview">
                    <flux:button type="button" variant="ghost">
                        {{ __('Preview badge') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:button type="button" variant="ghost" x-on:click="resetLayout()">
                    {{ __('Reset layout') }}
                </flux:button>

                @if ($canUpdate && $hasChanges)
                    <flux:button variant="primary" type="submit">
                        {{ __('Save badge template') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </div>

    <flux:modal name="badge-template-preview" class="max-w-3xl">
        <x-settings.site.badge-preview-modal-content
            :blocks="$blocks"
            :background-color="$backgroundColor"
            :name-panel-color="$namePanelColor"
            :title="$title"
            :subtitle="$subtitle"
            use-parent-state
            :preview-site-favicon-url="$previewSiteFaviconUrl"
            :preview-avatar-url="$previewAvatarUrl"
            :preview-christian-name="$previewChristianName"
            :preview-full-name="$previewFullName"
            :preview-qr-code-svg="$previewQrCodeSvg"
        />
    </flux:modal>
</div>
