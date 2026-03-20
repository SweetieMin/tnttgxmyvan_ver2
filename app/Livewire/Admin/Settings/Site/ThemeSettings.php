<?php

namespace App\Livewire\Admin\Settings\Site;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeSettings extends Component
{
    public string $preset = 'sky';

    public bool $seasonal_enabled = false;

    public function mount(): void
    {
        $this->preset = (string) ($this->settingValue('theme.preset') ?? 'sky');
        $this->seasonal_enabled = $this->settingValue('theme.seasonal_enabled') === '1';
    }

    public function selectPreset(string $preset): void
    {
        if (! array_key_exists($preset, $this->themePresets())) {
            return;
        }

        $this->preset = $preset;

        $this->dispatch('theme-preset-updated', preset: $preset);
    }

    public function updateThemeSettings(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate([
            'preset' => ['required', 'string'],
            'seasonal_enabled' => ['required', 'boolean'],
        ], [
            'preset.required' => __('Theme preset is required.'),
        ]);

        $this->upsertSetting('theme.preset', $validated['preset']);
        $this->upsertSetting('theme.seasonal_enabled', $validated['seasonal_enabled'] ? '1' : '0');

        $this->dispatch('theme-preset-updated', preset: $validated['preset']);

        Flux::toast(
            text: __('Theme settings updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function themePresets(): array
    {
        return [
            'base' => ['label' => 'Base', 'dot' => 'bg-zinc-700 border-zinc-700'],
            'red' => ['label' => 'Red', 'dot' => 'bg-red-500 border-red-500'],
            'orange' => ['label' => 'Orange', 'dot' => 'bg-orange-500 border-orange-500'],
            'amber' => ['label' => 'Amber', 'dot' => 'bg-amber-500 border-amber-500'],
            'yellow' => ['label' => 'Yellow', 'dot' => 'bg-yellow-400 border-yellow-400'],
            'lime' => ['label' => 'Lime', 'dot' => 'bg-lime-500 border-lime-500'],
            'green' => ['label' => 'Green', 'dot' => 'bg-green-600 border-green-600'],
            'emerald' => ['label' => 'Emerald', 'dot' => 'bg-emerald-500 border-emerald-500'],
            'teal' => ['label' => 'Teal', 'dot' => 'bg-teal-500 border-teal-500'],
            'cyan' => ['label' => 'Cyan', 'dot' => 'bg-cyan-500 border-cyan-500'],
            'sky' => ['label' => 'Sky', 'dot' => 'bg-sky-500 border-sky-500'],
            'blue' => ['label' => 'Blue', 'dot' => 'bg-blue-500 border-blue-500'],
            'indigo' => ['label' => 'Indigo', 'dot' => 'bg-indigo-500 border-indigo-500'],
            'violet' => ['label' => 'Violet', 'dot' => 'bg-violet-500 border-violet-500'],
            'purple' => ['label' => 'Purple', 'dot' => 'bg-purple-500 border-purple-500'],
            'fuchsia' => ['label' => 'Fuchsia', 'dot' => 'bg-fuchsia-500 border-fuchsia-500'],
            'pink' => ['label' => 'Pink', 'dot' => 'bg-pink-500 border-pink-500'],
            'rose' => ['label' => 'Rose', 'dot' => 'bg-rose-500 border-rose-500'],
        ];
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
            'theme.preset' => [
                'group' => 'theme',
                'type' => 'string',
                'label' => 'Mẫu giao diện',
                'description' => 'Mẫu giao diện được chọn để áp dụng màu sắc cho website.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 70,
            ],
            'theme.seasonal_enabled' => [
                'group' => 'theme',
                'type' => 'boolean',
                'label' => 'Bật giao diện theo mùa',
                'description' => 'Bật các giao diện đặc biệt cho mùa hoặc sự kiện.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 80,
            ],
        ];
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.site.theme.update'), 403);
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site.theme-settings', [
            'presets' => $this->themePresets(),
        ]);
    }
}
