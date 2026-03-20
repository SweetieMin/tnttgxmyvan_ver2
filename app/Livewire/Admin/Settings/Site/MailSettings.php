<?php

namespace App\Livewire\Admin\Settings\Site;

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MailSettings extends Component
{
    public string $from_address = '';

    public string $from_name = '';

    public string $reply_to_address = '';

    public string $username = '';

    public string $password = '';

    public bool $isHavePassword = false;

    public bool $showPasswordInput = false;

    public string $mailer = 'smtp';

    public string $host = '';

    public string $encryption = 'tls';

    public int $port = 587;

    public function mount(): void
    {
        $this->from_address = (string) ($this->settingValue('mail.from_address') ?? '');
        $this->from_name = (string) ($this->settingValue('mail.from_name') ?? '');
        $this->reply_to_address = (string) ($this->settingValue('mail.reply_to_address') ?? '');
        $this->username = (string) ($this->settingValue('mail.username') ?? '');
        $this->password = '';
        $this->isHavePassword = filled($this->settingValue('mail.password'));
        $this->mailer = (string) ($this->settingValue('mail.mailer') ?? 'smtp');
        $this->host = (string) ($this->settingValue('mail.host') ?? '');
        $this->encryption = (string) ($this->settingValue('mail.encryption') ?? 'tls');
        $this->port = (int) ($this->settingValue('mail.port') ?? 587);
    }

    public function togglePasswordInput(): void
    {
        $this->showPasswordInput = ! $this->showPasswordInput;

        if (! $this->showPasswordInput) {
            $this->password = '';
            $this->resetErrorBag('password');
        }
    }

    public function updatedEncryption(string $value): void
    {
        if ($value === 'ssl') {
            $this->port = 465;

            return;
        }

        if ($value === 'tls') {
            $this->port = 587;
        }
    }

    public function updateEmailSettings(): void
    {
        $this->ensureCanUpdate();

        $validated = $this->validate([
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'reply_to_address' => ['nullable', 'email', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'mailer' => ['required', 'string', 'max:50'],
            'host' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'string', 'max:50'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
        ], [
            'from_address.required' => __('From address is required.'),
            'from_name.required' => __('From name is required.'),
        ]);

        $this->upsertSetting('mail.from_address', $validated['from_address']);
        $this->upsertSetting('mail.from_name', $validated['from_name']);
        $this->upsertSetting('mail.reply_to_address', $validated['reply_to_address']);
        $this->upsertSetting('mail.username', $validated['username']);
        $this->upsertSetting('mail.mailer', $validated['mailer']);
        $this->upsertSetting('mail.host', $validated['host']);
        $this->upsertSetting('mail.encryption', $validated['encryption']);
        $this->upsertSetting('mail.port', (string) $validated['port']);

        if (filled($validated['password'])) {
            $this->upsertSetting('mail.password', $validated['password']);
            $this->isHavePassword = true;
            $this->showPasswordInput = false;
            $this->password = '';
        }

        Flux::toast(
            text: __('Mail settings updated successfully.'),
            heading: __('Success'),
            variant: 'success',
        );
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
            'mail.from_address' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Địa chỉ email gửi đi',
                'description' => 'Địa chỉ email người gửi mặc định cho các email được gửi đi.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 100,
            ],
            'mail.from_name' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Tên người gửi email',
                'description' => 'Tên người gửi mặc định cho các email được gửi đi.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 90,
            ],
            'mail.reply_to_address' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Địa chỉ phản hồi',
                'description' => 'Địa chỉ được dùng khi người nhận trả lời email.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 110,
            ],
            'mail.username' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Tên đăng nhập SMTP',
                'description' => 'Tên đăng nhập dùng để xác thực với máy chủ SMTP.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 120,
            ],
            'mail.password' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mật khẩu SMTP',
                'description' => 'Mật khẩu dùng để xác thực với máy chủ SMTP.',
                'is_public' => false,
                'is_encrypted' => true,
                'autoload' => false,
                'sort_order' => 130,
            ],
            'mail.mailer' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Giao thức gửi mail',
                'description' => 'Giao thức gửi email mặc định của hệ thống.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 140,
            ],
            'mail.host' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Máy chủ SMTP',
                'description' => 'Tên máy chủ SMTP dùng để gửi email.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 150,
            ],
            'mail.encryption' => [
                'group' => 'mail',
                'type' => 'string',
                'label' => 'Mã hóa kết nối',
                'description' => 'Kiểu mã hóa kết nối tới máy chủ SMTP.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 160,
            ],
            'mail.port' => [
                'group' => 'mail',
                'type' => 'integer',
                'label' => 'Cổng SMTP',
                'description' => 'Cổng kết nối tới máy chủ SMTP.',
                'is_public' => false,
                'is_encrypted' => false,
                'autoload' => true,
                'sort_order' => 170,
            ],
        ];
    }

    protected function ensureCanUpdate(): void
    {
        abort_unless((bool) Auth::user()?->can('settings.site.email.update'), 403);
    }

    public function render(): View
    {
        return view('livewire.admin.settings.site.mail-settings');
    }
}
