<?php

namespace App\Validation\Admin\Settings\Site;

class GeneralSettingsRules
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:255'],
            'site_meta_keywords' => ['nullable', 'string', 'max:1000'],
            'site_meta_description' => ['nullable', 'string', 'max:2000'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tikTok_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'site_title.required' => __('Site title is required.'),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function logoRules(): array
    {
        return [
            'site_logo' => ['required', 'image', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function logoMessages(): array
    {
        return [
            'site_logo.required' => __('Please choose a logo image.'),
            'site_logo.image' => __('The logo must be an image.'),
            'site_logo.max' => __('The logo may not be greater than 10 MB.'),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function faviconRules(): array
    {
        return [
            'site_favicon' => ['required', 'image', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function faviconMessages(): array
    {
        return [
            'site_favicon.required' => __('Please choose a favicon image.'),
            'site_favicon.image' => __('The favicon must be an image.'),
            'site_favicon.max' => __('The favicon may not be greater than 10 MB.'),
        ];
    }
}
