<?php

namespace App\Livewire\Admin\Settings\Site;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    #[Url(except: 'general')]
    public string $tab = 'general';

    public string $site_title = '';

    public string $site_email = '';

    public string $site_phone = '';

    public string $site_meta_keywords = '';

    public string $site_meta_description = '';

    public string $facebook_url = '';

    public string $instagram_url = '';

    public string $youtube_url = '';

    public string $tikTok_url = '';

    public ?string $existLogo = null;

    public ?string $existFavicon = null;

    public string $namePhotoDelete = '';

    public ?string $deletingImageKey = null;

    public ?TemporaryUploadedFile $site_logo = null;

    public ?TemporaryUploadedFile $site_favicon = null;

    public function mount(): void
    {
        $this->fillForm();
    }

    public function selectTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function updateGeneralSettings(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate([
            'site_title' => ['required', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:255'],
            'site_meta_keywords' => ['nullable', 'string', 'max:1000'],
            'site_meta_description' => ['nullable', 'string', 'max:2000'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tikTok_url' => ['nullable', 'url', 'max:255'],
        ], [
            'site_title.required' => __('Site title is required.'),
        ]);

        foreach ($this->generalSettingMap() as $property => $key) {
            $this->upsertSetting($key, $validated[$property] ?? null);
        }

        Flux::toast(
            text: __('General settings updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function saveLogo(): void
    {
        $this->ensureCanUpdate();

        $this->validate([
            'site_logo' => ['required', 'image', 'max:10240'],
        ], [
            'site_logo.required' => __('Please choose a logo image.'),
            'site_logo.image' => __('The logo must be an image.'),
            'site_logo.max' => __('The logo may not be greater than 10 MB.'),
        ]);

        $path = $this->site_logo?->store('images/sites', 'public');

        $this->upsertSetting('branding.logo', $path);
        $this->existLogo = $path;
        $this->reset('site_logo');
        $this->resetErrorBag('site_logo');

        Flux::toast(
            text: __('Logo updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function saveFavicon(): void
    {
        $this->ensureCanUpdate();

        $this->validate([
            'site_favicon' => ['required', 'image', 'max:10240'],
        ], [
            'site_favicon.required' => __('Please choose a favicon image.'),
            'site_favicon.image' => __('The favicon must be an image.'),
            'site_favicon.max' => __('The favicon may not be greater than 10 MB.'),
        ]);

        $path = $this->site_favicon?->store('images/sites', 'public');

        $this->upsertSetting('branding.favicon', $path);
        $this->existFavicon = $path;
        $this->reset('site_favicon');
        $this->resetErrorBag('site_favicon');

        Flux::toast(
            text: __('Favicon updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function removeLogoUpload(): void
    {
        $this->reset('site_logo');
        $this->resetErrorBag('site_logo');
    }

    public function removeFaviconUpload(): void
    {
        $this->reset('site_favicon');
        $this->resetErrorBag('site_favicon');
    }

    public function removeLogo(): void
    {
        $this->ensureCanUpdate();

        $this->deletingImageKey = 'branding.logo';
        $this->namePhotoDelete = __('Logo');

        $this->modal('settings-site-image')->show();
    }

    public function removeFavicon(): void
    {
        $this->ensureCanUpdate();

        $this->deletingImageKey = 'branding.favicon';
        $this->namePhotoDelete = __('Favicon');

        $this->modal('settings-site-image')->show();
    }

    public function deleteConfirm(): void
    {
        $this->ensureCanUpdate();

        if ($this->deletingImageKey === null) {
            return;
        }

        $this->upsertSetting($this->deletingImageKey, null);

        if ($this->deletingImageKey === 'branding.logo') {
            $this->existLogo = null;
        }

        if ($this->deletingImageKey === 'branding.favicon') {
            $this->existFavicon = null;
        }

        $this->deletingImageKey = null;
        $this->namePhotoDelete = '';

        $this->modal('settings-site-image')->close();

        Flux::toast(
            text: __('Image removed successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    /**
     * @return array<string, string>
     */
    protected function generalSettingMap(): array
    {
        return [
            'site_title' => 'general.site_name',
            'site_email' => 'general.site_email',
            'site_phone' => 'general.site_phone',
            'site_meta_keywords' => 'general.meta_keywords',
            'site_meta_description' => 'general.meta_description',
            'facebook_url' => 'social.facebook_url',
            'instagram_url' => 'social.instagram_url',
            'youtube_url' => 'social.youtube_url',
            'tikTok_url' => 'social.tiktok_url',
        ];
    }

    protected function fillForm(): void
    {
        $this->site_title = (string) ($this->settingValue('general.site_name') ?? '');
        $this->site_email = (string) ($this->settingValue('general.site_email') ?? '');
        $this->site_phone = (string) ($this->settingValue('general.site_phone') ?? '');
        $this->site_meta_keywords = (string) ($this->settingValue('general.meta_keywords') ?? '');
        $this->site_meta_description = (string) ($this->settingValue('general.meta_description') ?? '');
        $this->facebook_url = (string) ($this->settingValue('social.facebook_url') ?? '');
        $this->instagram_url = (string) ($this->settingValue('social.instagram_url') ?? '');
        $this->youtube_url = (string) ($this->settingValue('social.youtube_url') ?? '');
        $this->tikTok_url = (string) ($this->settingValue('social.tiktok_url') ?? '');
        $this->existLogo = $this->settingValue('branding.logo');
        $this->existFavicon = $this->settingValue('branding.favicon');
    }

    protected function settingValue(string $key): ?string
    {
        return Setting::query()
            ->where('key', $key)
            ->first()
            ?->value;
    }

    protected function upsertSetting(string $key, ?string $value): void
    {
        $definition = $this->settingDefinitions()[$key];

        Setting::query()->updateOrCreate(
            ['key' => $key],
            array_merge($definition, ['value' => $value]),
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function settingDefinitions(): array
    {
        return [
            'general.site_name' => [
                'group' => 'general',
                'type' => 'string',
                'label' => 'Site title',
                'description' => 'The public website title shown in browser titles and brand surfaces.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 10,
            ],
            'general.site_email' => [
                'group' => 'general',
                'type' => 'string',
                'label' => 'Site email',
                'description' => 'Primary public contact email for the website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 20,
            ],
            'general.site_phone' => [
                'group' => 'general',
                'type' => 'string',
                'label' => 'Site phone',
                'description' => 'Primary public contact phone number for the website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 30,
            ],
            'general.meta_keywords' => [
                'group' => 'general',
                'type' => 'string',
                'label' => 'Meta keywords',
                'description' => 'SEO keywords used for the public website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 40,
            ],
            'general.meta_description' => [
                'group' => 'general',
                'type' => 'string',
                'label' => 'Meta description',
                'description' => 'SEO description used for the public website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 50,
            ],
            'social.facebook_url' => [
                'group' => 'social',
                'type' => 'string',
                'label' => 'Facebook URL',
                'description' => 'Public Facebook page link for the website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 60,
            ],
            'social.instagram_url' => [
                'group' => 'social',
                'type' => 'string',
                'label' => 'Instagram URL',
                'description' => 'Public Instagram page link for the website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 70,
            ],
            'social.youtube_url' => [
                'group' => 'social',
                'type' => 'string',
                'label' => 'YouTube URL',
                'description' => 'Public YouTube channel link for the website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 80,
            ],
            'social.tiktok_url' => [
                'group' => 'social',
                'type' => 'string',
                'label' => 'TikTok URL',
                'description' => 'Public TikTok profile link for the website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 90,
            ],
            'branding.logo' => [
                'group' => 'branding',
                'type' => 'image',
                'label' => 'Logo',
                'description' => 'Primary brand image used in the app shell.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 100,
            ],
            'branding.favicon' => [
                'group' => 'branding',
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'Browser icon shown for the site.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 110,
            ],
        ];
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.site.general.update'), 403);
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site.general-settings');
    }
}
