<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'group' => 'general',
                'key' => 'general.site_name',
                'value' => 'TNTT Gx My Van',
                'type' => 'string',
                'label' => 'Site name',
                'description' => 'The public website name shown in headings and browser titles.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 10,
            ],
            [
                'group' => 'general',
                'key' => 'general.site_tagline',
                'value' => 'Parish youth management platform',
                'type' => 'string',
                'label' => 'Site tagline',
                'description' => 'Short description used in login and brand surfaces.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 20,
            ],
            [
                'group' => 'branding',
                'key' => 'branding.logo',
                'value' => 'images/sites/FAVICON_default.png',
                'type' => 'image',
                'label' => 'Logo',
                'description' => 'Primary brand image used in the app shell.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 30,
            ],
            [
                'group' => 'branding',
                'key' => 'branding.favicon',
                'value' => 'images/sites/FAVICON_default.png',
                'type' => 'image',
                'label' => 'Favicon',
                'description' => 'Browser icon shown for the site.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 40,
            ],
            [
                'group' => 'branding',
                'key' => 'branding.login_image',
                'value' => 'images/sites/login_default.png',
                'type' => 'image',
                'label' => 'Login image',
                'description' => 'Image shown on the authentication split layout.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 50,
            ],
            [
                'group' => 'theme',
                'key' => 'theme.mode',
                'value' => 'light',
                'type' => 'string',
                'label' => 'Theme mode',
                'description' => 'Default appearance mode for the system.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 60,
            ],
            [
                'group' => 'theme',
                'key' => 'theme.preset',
                'value' => 'default',
                'type' => 'string',
                'label' => 'Theme preset',
                'description' => 'Selected preset used for the site color theme.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 70,
            ],
            [
                'group' => 'theme',
                'key' => 'theme.seasonal_enabled',
                'value' => '0',
                'type' => 'boolean',
                'label' => 'Seasonal theme enabled',
                'description' => 'Enable special themes for seasonal events.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 80,
            ],
            [
                'group' => 'mail',
                'key' => 'mail.from_name',
                'value' => 'TNTT Gx My Van',
                'type' => 'string',
                'label' => 'Mail from name',
                'description' => 'Default sender name for outgoing emails.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 90,
            ],
            [
                'group' => 'mail',
                'key' => 'mail.from_address',
                'value' => 'noreply@example.com',
                'type' => 'string',
                'label' => 'Mail from address',
                'description' => 'Default sender email address for outgoing emails.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 100,
            ],
            [
                'group' => 'mail',
                'key' => 'mail.reply_to_address',
                'value' => 'tntt.myvan@gmail.com',
                'type' => 'string',
                'label' => 'Reply-to address',
                'description' => 'Address used when recipients reply to outgoing emails.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 110,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting,
            );
        }
    }
}
