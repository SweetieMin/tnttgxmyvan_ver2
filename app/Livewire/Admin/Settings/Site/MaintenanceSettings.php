<?php

namespace App\Livewire\Admin\Settings\Site;

use App\Models\Setting;
use App\Validation\Admin\Settings\Site\MaintenanceSettingsRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MaintenanceSettings extends Component
{
    #[Validate]
    public bool $is_maintenance = false;

    public bool $app_is_in_maintenance = false;

    #[Validate]
    public string $secret_key = '';

    #[Validate]
    public string $message = '';

    #[Validate]
    public string $start_at = '';

    #[Validate]
    public string $end_at = '';

    /**
     * @var array<string, mixed>
     */
    #[Locked]
    public array $originalMaintenanceSettings = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function updatedIsMaintenance(bool $value): void
    {
        $this->is_maintenance = $value;

        if (! $value) {
            return;
        }

        $this->secret_key = Str::orderedUuid()->toString();

        $start = now()->ceilHour();

        $this->start_at = $start->format('H:i');
        $this->end_at = $start->copy()->addHours(4)->format('H:i');
    }

    public function updateMaintenanceSettings(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate();

        if ($validated['is_maintenance'] && blank($validated['secret_key'])) {
            $validated['secret_key'] = Str::orderedUuid()->toString();
            $this->secret_key = $validated['secret_key'];
        }

        if ($validated['is_maintenance']) {
            $this->upsertSetting('maintenance.enabled', '1');
            $this->upsertSetting('maintenance.secret_key', $validated['secret_key'] ?? '');
            $this->upsertSetting('maintenance.message', $validated['message'] ?? '');
            $this->upsertSetting('maintenance.start_at', $validated['start_at'] ?? '');
            $this->upsertSetting('maintenance.end_at', $validated['end_at'] ?? '');

            $this->modal('maintenance-confirm')->show();

            return;
        }

        $this->upsertSetting('maintenance.enabled', '0');
        $this->upsertSetting('maintenance.secret_key', '');
        $this->upsertSetting('maintenance.message', '');
        $this->upsertSetting('maintenance.start_at', '');
        $this->upsertSetting('maintenance.end_at', '');

        if ($this->app_is_in_maintenance) {
            Artisan::call('up');
        }

        $this->secret_key = '';
        $this->message = '';
        $this->start_at = '';
        $this->end_at = '';
        $this->app_is_in_maintenance = false;

        Flux::toast(
            text: __('The system is now online.'),
            heading: __('Success'),
            variant: 'success',
        );

        $this->syncOriginalMaintenanceSettings();
    }

    public function enableMaintenanceConfirm(): void
    {
        $this->ensureCanUpdate();

        Artisan::call("down --secret={$this->secret_key}");

        $this->app_is_in_maintenance = true;

        $this->modal('maintenance-confirm')->close();

        $this->redirect("/{$this->secret_key}", navigate: true);
    }

    public function loadData(): void
    {
        $this->app_is_in_maintenance = app()->isDownForMaintenance();

        $this->is_maintenance = $this->settingValue('maintenance.enabled') === '1';
        $this->secret_key = (string) ($this->settingValue('maintenance.secret_key') ?? '');
        $this->message = (string) ($this->settingValue('maintenance.message') ?? '');
        $this->start_at = (string) ($this->settingValue('maintenance.start_at') ?? '');
        $this->end_at = (string) ($this->settingValue('maintenance.end_at') ?? '');
        $this->syncOriginalMaintenanceSettings();
    }

    public function hasMaintenanceChanges(): bool
    {
        return $this->currentMaintenanceSettings() !== $this->originalMaintenanceSettings;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return MaintenanceSettingsRules::rules();
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return MaintenanceSettingsRules::messages();
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
            'maintenance.enabled' => [
                'group' => 'maintenance',
                'type' => 'boolean',
                'label' => 'Bật bảo trì',
                'description' => 'Trạng thái bật hoặc tắt chế độ bảo trì của hệ thống.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 180,
            ],
            'maintenance.secret_key' => [
                'group' => 'maintenance',
                'type' => 'string',
                'label' => 'Khóa bí mật bảo trì',
                'description' => 'Khóa bí mật dùng để truy cập hệ thống khi đang bảo trì.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => false,
                'sort_order' => 190,
            ],
            'maintenance.message' => [
                'group' => 'maintenance',
                'type' => 'string',
                'label' => 'Thông điệp bảo trì',
                'description' => 'Thông điệp hiển thị khi hệ thống đang ở chế độ bảo trì.',
                'is_public' => true,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 200,
            ],
            'maintenance.start_at' => [
                'group' => 'maintenance',
                'type' => 'string',
                'label' => 'Thời gian bắt đầu bảo trì',
                'description' => 'Thời điểm bắt đầu áp dụng chế độ bảo trì.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 210,
            ],
            'maintenance.end_at' => [
                'group' => 'maintenance',
                'type' => 'string',
                'label' => 'Thời gian kết thúc bảo trì',
                'description' => 'Thời điểm kết thúc chế độ bảo trì.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 220,
            ],
        ];
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.site.maintenance.update'), 403);
    }

    protected function syncOriginalMaintenanceSettings(): void
    {
        $this->originalMaintenanceSettings = $this->currentMaintenanceSettings();
    }

    /**
     * @return array<string, mixed>
     */
    protected function currentMaintenanceSettings(): array
    {
        return [
            'is_maintenance' => $this->is_maintenance,
            'secret_key' => $this->secret_key,
            'message' => $this->message,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site.maintenance-settings');
    }
}
