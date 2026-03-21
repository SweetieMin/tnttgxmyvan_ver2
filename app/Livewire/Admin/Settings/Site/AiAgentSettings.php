<?php

namespace App\Livewire\Admin\Settings\Site;

use App\Models\Setting;
use App\Validation\Admin\Settings\Site\AiAgentSettingsRules;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AiAgentSettings extends Component
{
    public ?int $editingSettingId = null;

    public string $editingKey = '';

    public string $editingGroup = '';

    public string $editingLabel = '';

    public bool $editingIsEncrypted = false;

    #[Validate]
    public string $editingValue = '';

    #[Locked]
    public string $originalEditingValue = '';

    public function mount(): void
    {
        $this->ensureAgentSettingsExist();
    }

    public function editSetting(int $settingId): void
    {
        $this->ensureCanView();

        $setting = $this->agentSettings()
            ->firstWhere('id', $settingId);

        abort_if($setting === null, 404);

        $this->editingSettingId = $setting->id;
        $this->editingKey = $setting->key;
        $this->editingGroup = $setting->group;
        $this->editingLabel = $setting->label;
        $this->editingIsEncrypted = $setting->is_encrypted;
        $this->editingValue = (string) ($setting->value ?? '');
        $this->originalEditingValue = $this->editingValue;
        $this->resetErrorBag();

        $this->modal('edit-agent-setting')->show();
    }

    public function updateAgentSetting(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate();

        if ($this->editingSettingId === null) {
            return;
        }

        $setting = Setting::query()
            ->whereKey($this->editingSettingId)
            ->where('group', 'like', 'agent.%')
            ->firstOrFail();

        $setting->update([
            'value' => $validated['editingValue'],
        ]);

        $this->originalEditingValue = $this->editingValue;

        $this->modal('edit-agent-setting')->close();

        Flux::toast(
            text: __('AI agent setting updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
    }

    public function hasEditingChanges(): bool
    {
        return $this->editingValue !== $this->originalEditingValue;
    }

    /**
     * @return Collection<int, Setting>
     */
    public function agentSettings(): Collection
    {
        return Setting::query()
            ->where('group', 'like', 'agent.%')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    public function displaySettingValue(Setting $setting): string
    {
        $value = (string) ($setting->value ?? '');

        if ($value === '') {
            return __('Not configured');
        }

        if ($setting->is_encrypted) {
            if (Str::length($value) <= 4) {
                return str_repeat('*', Str::length($value));
            }

            return str_repeat('*', max(Str::length($value) - 4, 4)).Str::substr($value, -4);
        }

        return $value;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return AiAgentSettingsRules::rules($this->editingKey);
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return AiAgentSettingsRules::messages();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function settingDefinitions(): array
    {
        return [
            'agent.transaction_file_checker.api_key' => [
                'group' => 'agent.transaction_file_checker',
                'type' => 'string',
                'value' => '',
                'label' => 'API key',
                'description' => 'Khóa API dùng cho AI Agent kiểm tra chứng từ quỹ chung.',
                'is_public' => false,
                'is_encrypted' => true,
                'autoload' => false,
                'sort_order' => 10,
            ],
            'agent.transaction_file_checker.base_url' => [
                'group' => 'agent.transaction_file_checker',
                'type' => 'string',
                'value' => '',
                'label' => 'Base URL',
                'description' => 'Địa chỉ nền dùng để gọi AI Agent kiểm tra chứng từ quỹ chung.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => false,
                'sort_order' => 20,
            ],
        ];
    }

    protected function ensureAgentSettingsExist(): void
    {
        foreach ($this->settingDefinitions() as $key => $definition) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                $definition,
            );
        }
    }

    protected function ensureCanView(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.site.ai-agent.view'), 403);
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.site.ai-agent.update'), 403);
    }

    public function render(): View
    {
        $this->ensureCanView();

        return view('livewire.admin.settings.site.ai-agent-settings');
    }
}
