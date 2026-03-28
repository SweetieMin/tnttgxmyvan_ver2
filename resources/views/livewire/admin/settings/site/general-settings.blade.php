<section class="w-full">
    @include('partials.site-settings-heading')


    <x-layouts::settings.site.layout :heading="__('General configuration')" :subheading="__('Update the general system settings')">

        <flux:tab.group>

            <flux:tabs scrollable scrollable:fade>

                <flux:tab wire:click="selectTab('general')" name="general" icon="cog-6-tooth"
                    :selected="$tab == 'general'">{{ __('General') }}</flux:tab>
                <flux:tab wire:click="selectTab('badge-template')" name="badge-template" icon="identification"
                    :selected="$tab == 'badge-template'">{{ __('Badge card configuration') }}</flux:tab>
                <flux:tab wire:click="selectTab('logo-favicon')" name="logo-favicon" icon="globe-alt"
                    :selected="$tab == 'logo-favicon'">{{ __('Logo & Favicon') }}</flux:tab>
                <flux:tab wire:click="selectTab('login-image')" name="login-image" icon="photo"
                    :selected="$tab == 'login-image'">{{ __('Login image') }}</flux:tab>

            </flux:tabs>

            <flux:tab.panel name="general">
                <form wire:submit.prevent="updateGeneralSettings()" class="w-full">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-8 items-start ">

                        {{-- Cột trái: Cài đặt chung --}}
                        <div class="space-y-6">
                            <flux:separator :text="__('General configuration')" class="my-2" />
                            <flux:input wire:model.live.debounce.500ms="site_title" :label="__('Site title')" type="text" autofocus
                                x-data x-init="$nextTick(() => $el.focus())" />
                            <flux:input wire:model.live.debounce.500ms="site_email" :label="__('Email')" type="email" autofocus />
                            <flux:input wire:model.live.debounce.500ms="site_phone" :label="__('Phone')" type="text" />
                            <flux:input wire:model.live.debounce.500ms="site_meta_keywords" :label="__('Meta keywords')"
                                type="text" />

                        </div>

                        {{-- Separator dọc --}}
                        <flux:separator vertical class="hidden md:block" />

                        {{-- Cột phải: Liên kết --}}
                        <div class="space-y-6">
                            <flux:separator :text="__('Link')" class="my-2" />
                            <flux:input wire:model.live.debounce.500ms="facebook_url" :label="__('URL Facebook')" type="text" />
                            <flux:input wire:model.live.debounce.500ms="instagram_url" :label="__('URL Instagram')" type="text" />
                            <flux:input wire:model.live.debounce.500ms="youtube_url" :label="__('URL YouTube')" type="text" />
                            <flux:input wire:model.live.debounce.500ms="tikTok_url" :label="__('URL TikTok')" type="text" />
                        </div>

                    </div>
                    <div class="my-2">
                        <flux:textarea wire:model.live.debounce.500ms="site_meta_description" :label="__('Meta description')"
                            class="min-h-30" />

                    </div>
                    <flux:separator class="my-2" />
                    {{-- Nút lưu --}}
                    <div class="mt-8 flex items-center gap-4">
                        @can('settings.site.general.update')
                            @if ($this->hasGeneralChanges())
                                <flux:button variant="primary" type="submit" class="cursor-pointer">
                                    {{ __('Save') }}
                                </flux:button>
                            @endif
                        @endcan
                    </div>
                </form>
            </flux:tab.panel>

            <flux:tab.panel name="badge-template">
                <x-settings.site.badge-template-editor
                    :blocks="$this->badgeTemplateBlocks()"
                    :background-color="$badge_background_color"
                    :name-panel-color="$badge_name_panel_color"
                    :title="$badge_title"
                    :subtitle="$badge_subtitle"
                    :preview-site-favicon-url="$this->previewSiteFaviconUrl()"
                    :preview-avatar-url="$this->previewAvatarUrl()"
                    :preview-christian-name="$this->previewChristianName()"
                    :preview-full-name="$this->previewFullName()"
                    :preview-qr-code-svg="$this->previewQrCodeSvg()"
                    :can-update="auth()->user()?->can('settings.site.general.update') ?? false"
                    :has-changes="$this->hasBadgeTemplateChanges()"
                />
            </flux:tab.panel>

            <flux:tab.panel name="logo-favicon">

                <div class="w-full">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-8 items-start">

                        {{-- Cột trái: Cài đặt chung --}}
                        <div class="space-y-6">
                            <flux:separator :text="__('Open Graph (OG)')" class="my-2" />

                            <!-- Blade view: -->

                            <form wire:submit="saveLogo">

                                {{-- 1️⃣ Nếu đang chọn logo mới --}}
                                @if ($site_logo)
                                    <div class="relative mb-2">
                                        <img src="{{ $site_logo->temporaryUrl() }}"
                                            class="w-full h-auto max-h-[80vh] border-2 border-(--color-accent-content) rounded-xl object-cover">
                                        @can('settings.site.general.update')
                                            <flux:button size="sm" variant="primary" color="green" type="submit"
                                                icon="check" class="absolute! top-2 left-2 cursor-pointer" />
                                            <!-- Nút xoá -->
                                            <flux:button size="sm" variant="primary" color="rose" type="button"
                                                icon="trash" class="absolute! top-2 left-12 cursor-pointer"
                                                wire:click="removeLogoUpload" />
                                        @endcan
                                    </div>

                                    <flux:error name="site_logo" />

                                    <flux:callout class="mb-2" variant="warning" icon="exclamation-circle"
                                        :heading="__('Please click the button on the image to save the new file.')" />
                                    {{-- 2️⃣ Nếu chưa chọn mới nhưng có logo cũ --}}
                                @elseif ($existLogo)
                                    <div class="relative mb-2 inline-block group">
                                        <img src="{{ Storage::url($existLogo) }}"
                                            class="max-h-50 md:max-h-80 border-2 border-(--color-accent-content) rounded-xl object-cover">

                                        @can('settings.site.general.update')
                                            <flux:tooltip content="{{ __('Remove current logo') }}" placement="top">
                                                <flux:button size="sm" variant="primary" color="rose" icon="x-mark"
                                                    wire:click="removeLogo"
                                                    class="absolute! top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                                            </flux:tooltip>
                                        @endcan
                                    </div>

                                    {{-- 3️⃣ Nếu chưa có logo nào --}}
                                @else
                                    @can('settings.site.general.update')
                                        <flux:file-upload wire:model.live.lazy="site_logo" :label="__('Upload file')"
                                            accept=".jpg, .png">

                                            <flux:file-upload.dropzone :heading="__('Drop a file here or click to browse')"
                                                :text="__('JPG, PNG up to 10MB')" with-progress inline />
                                        </flux:file-upload>
                                    @endcan
                                @endif



                            </form>

                        </div>

                        {{-- Separator dọc --}}
                        <flux:separator vertical class="hidden md:block" />

                        {{-- Cột phải: Liên kết --}}
                        <div class="space-y-6">
                            <flux:separator text="Favicon" class="my-2" />

                            <form wire:submit="saveFavicon">

                                {{-- 1️⃣ Nếu đang chọn Favicon mới --}}
                                @if ($site_favicon)
                                    <div class="relative mb-2">
                                        <img src="{{ $site_favicon->temporaryUrl() }}" class="max-h-85 rounded-xl">
                                        @can('settings.site.general.update')
                                            <flux:button size="sm" variant="primary" color="green" type="submit"
                                                icon="check" class="absolute! top-2 left-2 cursor-pointer" />

                                            <!-- Nút xoá -->
                                            <flux:button size="sm" variant="primary" color="rose" type="button"
                                                icon="trash" class="absolute! top-2 left-12 cursor-pointer"
                                                wire:click="removeFaviconUpload" />
                                        @endcan
                                    </div>

                                    <flux:error name="site_favicon" />

                                    <flux:callout class="mb-2" variant="warning" icon="exclamation-circle"
                                        :heading="__('Please click the button on the image to save the new file.')" />
                                    {{-- 2️⃣ Nếu chưa chọn mới nhưng có Favicon cũ --}}
                                @elseif ($existFavicon)
                                    <div class="relative mb-2 group inline-block">
                                        <img src="{{ Storage::url($existFavicon) }}"
                                            class="max-h-50 md:max-h-80 border-2 border-(--color-accent-content) rounded-xl object-cover">

                                        @can('settings.site.general.update')
                                            <flux:tooltip content="{{ __('Remove current favicon') }}" placement="top">
                                                <flux:button size="sm" variant="primary" color="rose"
                                                    icon="x-mark" wire:click="removeFavicon"
                                                    class="absolute! top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                                            </flux:tooltip>
                                        @endcan
                                    </div>

                                    {{-- 3️⃣ Nếu chưa có Favicon nào --}}
                                @else
                                    @can('settings.site.general.update')
                                        <flux:file-upload wire:model="site_favicon" :label="__('Upload file')"
                                            accept=".jpg, .png">

                                            <flux:file-upload.dropzone :heading="__('Drop a file here or click to browse')"
                                                :text="__('JPG, PNG up to 10MB')" with-progress inline />
                                        </flux:file-upload>
                                    @endcan
                                @endif



                            </form>


                        </div>

                    </div>


                    {{-- Nút lưu --}}

                </div>

                <flux:separator class="mb-2" />

                <flux:modal.trigger name="site-image-guide">
                    <flux:button variant="ghost" icon="information-circle" class="w-full justify-start text-left md:w-auto">
                        <span class="whitespace-normal">
                            {{ __('How to update Open Graph (OG) and favicon images.') }}
                        </span>
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="site-image-guide" flyout variant="floating" class="md:w-2xl" position="left">
                    <div class="space-y-6">
                        <flux:heading size="lg">{{ __('How to update Open Graph (OG) and favicon images.') }}</flux:heading>

                        <div class="space-y-4 text-sm leading-relaxed">
                            <div>
                                <h3 class="font-semibold text-base mb-1">{{ __('1. Add or update images (OG / Favicon)') }}</h3>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>{{ __('Click the') }} <strong>"{{ __('Upload file') }}"</strong> {{ __('area or drag and drop an image into the file picker.') }}</li>
                                    <li>{{ __('Only') }} <strong>JPG, PNG</strong> {{ __('formats are accepted (maximum size') }} <strong>10MB</strong>).</li>
                                    <li>{{ __('After selecting an image, the system will show a') }} <strong>{{ __('preview') }}</strong>.</li>
                                    <li>{{ __('Click the') }} <strong>{{ __('green check button') }}</strong> {{ __('in the top corner of the image to save the change.') }}</li>
                                    <li>{{ __('If you do not click save, the image will') }} <strong>{{ __('not be updated') }}</strong>.</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="font-semibold text-base mb-1">{{ __('2. Remove images') }}</h3>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>{{ __('If a current image exists, the system will display it.') }}</li>
                                    <li>{{ __('Click the') }} <strong>{{ __('red X button') }}</strong> {{ __('in the top corner of the image.') }}</li>
                                    <li>{{ __('Confirm the removal in the dialog.') }}</li>
                                    <li>{{ __('After the image is removed successfully, the data will refresh.') }}</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="font-semibold text-base mb-1">{{ __('3. Recommended image sizes') }}</h3>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>
                                        <strong>{{ __('Open Graph (OG):') }}</strong>
                                        {{ __('Use a') }} <strong>{{ __('rectangular image (1.91:1 ratio)') }}</strong>
                                        {{ __('to display nicely when sharing links to Facebook, Zalo, and similar platforms.') }}
                                        <br>
                                        {{ __('Recommended size:') }} <strong>1200 x 630px</strong>.
                                    </li>
                                    <li>
                                        <strong>{{ __('Favicon:') }}</strong>
                                        {{ __('Use a') }} <strong>{{ __('square image (1:1)') }}</strong>.
                                        <br>
                                        {{ __('Recommended size:') }} <strong>32x32px</strong> {{ __('or') }} <strong>64x64px</strong>.
                                    </li>
                                </ul>
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ __('After updating, if the image is not visible on the website or when sharing links, please refresh the browser (Ctrl + F5) or wait for the cache to update.') }}
                            </div>
                        </div>
                    </div>

                    <x-slot name="footer" class="flex items-center justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="filled">{{ __('Close') }}</flux:button>
                        </flux:modal.close>
                    </x-slot>
                </flux:modal>

            </flux:tab.panel>

            <flux:tab.panel name="login-image">
                <div class="w-full max-w-3xl">
                    <flux:separator :text="__('Login image')" class="my-2" />

                    <form wire:submit="saveLoginImage">
                        @if ($site_login_image)
                            <div class="relative mb-2">
                                <img src="{{ $site_login_image->temporaryUrl() }}"
                                    class="w-full h-auto max-h-[80vh] border-2 border-(--color-accent-content) rounded-xl object-cover">
                                @can('settings.site.general.update')
                                    <flux:button size="sm" variant="primary" color="green" type="submit"
                                        icon="check" class="absolute! top-2 left-2 cursor-pointer" />
                                    <flux:button size="sm" variant="primary" color="rose" type="button"
                                        icon="trash" class="absolute! top-2 left-12 cursor-pointer"
                                        wire:click="removeLoginImageUpload" />
                                @endcan
                            </div>

                            <flux:error name="site_login_image" />

                            <flux:callout class="mb-2" variant="warning" icon="exclamation-circle"
                                :heading="__('Please click the button on the image to save the new file.')" />
                        @elseif ($existLoginImage)
                            <div class="relative mb-2 inline-block group">
                                <img src="{{ Storage::url($existLoginImage) }}"
                                    class="max-h-80 border-2 border-(--color-accent-content) rounded-xl object-cover">

                                @can('settings.site.general.update')
                                    <flux:tooltip content="{{ __('Remove current login image') }}" placement="top">
                                        <flux:button size="sm" variant="primary" color="rose" icon="x-mark"
                                            wire:click="removeLoginImage"
                                            class="absolute! top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200" />
                                    </flux:tooltip>
                                @endcan
                            </div>
                        @else
                            @can('settings.site.general.update')
                                <flux:file-upload wire:model.live.lazy="site_login_image" :label="__('Upload file')"
                                    accept=".jpg, .png">
                                    <flux:file-upload.dropzone :heading="__('Drop a file here or click to browse')"
                                        :text="__('JPG, PNG up to 10MB')" with-progress inline />
                                </flux:file-upload>
                            @endcan
                        @endif
                    </form>
                </div>

            </flux:tab.panel>

        </flux:tab.group>


    </x-layouts::settings.site.layout>

    <flux:modal name="settings-site-image" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('Delete image :name?', ['name' => $namePhotoDelete]) }}
                </flux:heading>

                <flux:text class="mt-2">
                    {{ __('Are you sure you want to delete image :name from the system?', ['name' => $namePhotoDelete]) }}<br>
                    {{ __('This action cannot be undone.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                @can('settings.site.general.update')
                    <flux:button wire:click="deleteConfirm" type="submit" variant="primary" color="rose">
                        {{ __('Delete permanently') }}
                    </flux:button>
                @endcan
            </div>
        </div>
    </flux:modal>

</section>
