@props([
    'enabled' => false,
    'previewUrl' => null,
    'fileModel' => 'pictureUpload',
    'modalModel' => 'showAvatarCropModal',
    'previewModel' => 'cropPreviewUrl',
    'outputModel' => 'croppedImageData',
    'size' => 600,
])

<div class="space-y-4">
    @if ($enabled)
        <flux:file-upload wire:model="{{ $fileModel }}" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
            <div class="relative flex size-36 cursor-pointer items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 transition-colors hover:border-zinc-300 hover:bg-zinc-200 dark:border-white/10 dark:bg-white/10 dark:hover:border-white/20 dark:hover:bg-white/15">
                @if ($previewUrl)
                    <img src="{{ $previewUrl }}" class="size-full rounded-full object-cover" />
                @else
                    <flux:icon name="user" variant="solid" class="size-14 text-zinc-500 dark:text-zinc-400" />
                @endif

                <div class="absolute bottom-1 right-1 rounded-full bg-white dark:bg-zinc-800">
                    <flux:icon name="arrow-up-circle" variant="solid" class="size-7 text-zinc-500 dark:text-zinc-400" />
                </div>
            </div>
        </flux:file-upload>
    @else
        <div class="flex size-36 items-center justify-center rounded-full border border-zinc-200 bg-zinc-100 dark:border-white/10 dark:bg-white/10">
            @if ($previewUrl)
                <img src="{{ $previewUrl }}" class="size-full rounded-full object-cover" />
            @else
                <flux:icon name="user" variant="solid" class="size-14 text-zinc-500 dark:text-zinc-400" />
            @endif
        </div>
    @endif

    <flux:modal wire:model="{{ $modalModel }}" class="max-w-4xl">
        <div
            x-data="imageCropUpload({
                $wire,
                modalModel: '{{ $modalModel }}',
                previewModel: '{{ $previewModel }}',
                outputModel: '{{ $outputModel }}',
                size: {{ $size }},
            })"
            x-init="init()"
            class="space-y-5"
        >
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Adjust avatar') }}</flux:heading>
                <flux:text>{{ __('Drag and zoom the photo so the face fits inside the guide circle before saving.') }}</flux:text>
            </div>

            <div class="relative overflow-hidden rounded-3xl bg-zinc-950/95 p-4">
                <div wire:ignore x-ref="cropperHost" class="relative mx-auto aspect-square w-full max-w-2xl overflow-hidden rounded-2xl bg-zinc-950">
                    <img x-ref="image" x-bind:src="imageUrl || ''" alt="{{ __('Avatar crop preview') }}" class="block max-h-[70vh] w-full object-contain" />
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex gap-2">
                    <flux:button variant="ghost" icon="minus" x-on:click="zoomOut">
                        {{ __('Zoom out') }}
                    </flux:button>
                    <flux:button variant="ghost" icon="plus" x-on:click="zoomIn">
                        {{ __('Zoom in') }}
                    </flux:button>
                </div>

                <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="cancelAvatarCrop">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button variant="primary" x-on:click="saveCrop" x-bind:disabled="isSaving">
                    {{ __('Use this photo') }}
                </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
