<?php

namespace App\Livewire\Admin\Settings\Site;

use App\Models\Setting;
use App\Validation\Admin\Settings\Site\ThemeSettingsRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Title('Cấu hình giao diện')]
class ThemeSettings extends Component
{
    #[Validate]
    public string $preset = 'sky';

    #[Validate]
    public string $neutral_palette = 'gray';

    #[Validate]
    public bool $seasonal_enabled = false;

    /**
     * @var array<string, mixed>
     */
    #[Locked]
    public array $originalThemeSettings = [];

    public function mount(): void
    {
        $this->preset = (string) ($this->settingValue('theme.preset') ?? 'sky');
        $this->neutral_palette = (string) ($this->settingValue('theme.neutral_palette') ?? 'gray');
        $this->seasonal_enabled = $this->settingValue('theme.seasonal_enabled') === '1';
        $this->syncOriginalThemeSettings();
    }

    public function selectPreset(string $preset): void
    {
        if (! array_key_exists($preset, $this->themePresets())) {
            return;
        }

        $this->preset = $preset;

        $this->dispatch('theme-preset-updated', preset: $preset);
    }

    public function selectNeutralPalette(string $neutralPalette): void
    {
        if (! array_key_exists($neutralPalette, $this->neutralPalettes())) {
            return;
        }

        $this->neutral_palette = $neutralPalette;

        $this->dispatch('theme-neutral-palette-updated', neutralPalette: $neutralPalette);
    }

    public function updateThemeSettings(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate();

        $this->upsertSetting('theme.preset', $validated['preset']);
        $this->upsertSetting('theme.neutral_palette', $validated['neutral_palette']);
        $this->upsertSetting('theme.seasonal_enabled', $validated['seasonal_enabled'] ? '1' : '0');

        $this->dispatch('theme-preset-updated', preset: $validated['preset']);
        $this->dispatch('theme-neutral-palette-updated', neutralPalette: $validated['neutral_palette']);

        Flux::toast(
            text: __('Theme settings updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->syncOriginalThemeSettings();
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

    /**
     * @return array<string, array<string, string>>
     */
    public function neutralPalettes(): array
    {
        return [
            'slate' => ['label' => 'Slate', 'dot' => 'bg-slate-400 border-slate-400'],
            'gray' => ['label' => 'Gray', 'dot' => 'bg-gray-400 border-gray-400'],
            'zinc' => ['label' => 'Zinc', 'dot' => 'bg-zinc-400 border-zinc-400'],
            'neutral' => ['label' => 'Neutral', 'dot' => 'bg-neutral-400 border-neutral-400'],
            'stone' => ['label' => 'Stone', 'dot' => 'bg-stone-400 border-stone-400'],
            'mauve' => ['label' => 'Mauve', 'dot' => 'bg-[var(--color-mauve-400)] border-[var(--color-mauve-400)]'],
            'olive' => ['label' => 'Olive', 'dot' => 'bg-[var(--color-olive-400)] border-[var(--color-olive-400)]'],
            'mist' => ['label' => 'Mist', 'dot' => 'bg-[var(--color-mist-400)] border-[var(--color-mist-400)]'],
            'taupe' => ['label' => 'Taupe', 'dot' => 'bg-[var(--color-taupe-400)] border-[var(--color-taupe-400)]'],
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
            'theme.neutral_palette' => [
                'group' => 'theme',
                'type' => 'string',
                'label' => 'Bảng màu trung tính',
                'description' => 'Bảng màu trung tính được dùng cho nền và các tông xám của giao diện.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 75,
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

    public function hasThemeChanges(): bool
    {
        return $this->currentThemeSettings() !== $this->originalThemeSettings;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return ThemeSettingsRules::rules();
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return ThemeSettingsRules::messages();
    }

    protected function syncOriginalThemeSettings(): void
    {
        $this->originalThemeSettings = $this->currentThemeSettings();
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentThemeSettings(): array
    {
        return [
            'preset' => $this->preset,
            'neutral_palette' => $this->neutral_palette,
            'seasonal_enabled' => $this->seasonal_enabled,
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site.theme-settings', [
            'presets' => $this->themePresets(),
            'neutralPalettes' => $this->neutralPalettes(),
        ]);
    }
}
