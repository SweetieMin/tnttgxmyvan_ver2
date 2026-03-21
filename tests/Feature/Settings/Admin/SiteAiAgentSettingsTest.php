<?php

use App\Livewire\Admin\Settings\Site\AiAgentSettings;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    collect([
        'settings.site.ai-agent.view',
        'settings.site.ai-agent.update',
    ])->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));
});

test('authorized users can visit the ai agent settings page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.site.ai-agent.view');

    $response = $this->actingAs($user)->get(route('admin.settings.site.ai-agent'));

    $response->assertOk()
        ->assertSeeText(__('AI Agent configuration'))
        ->assertSeeText('agent.transaction_file_checker');
});

test('ai agent settings can be updated from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'settings.site.ai-agent.view',
        'settings.site.ai-agent.update',
    ]);

    $this->actingAs($user);

    $component = Livewire::test(AiAgentSettings::class);

    $baseUrlSetting = Setting::query()
        ->where('key', 'agent.transaction_file_checker.base_url')
        ->firstOrFail();

    $apiKeySetting = Setting::query()
        ->where('key', 'agent.transaction_file_checker.api_key')
        ->firstOrFail();

    $component
        ->call('editSetting', $baseUrlSetting->id)
        ->set('editingValue', 'https://api.example.com/v1')
        ->call('updateAgentSetting')
        ->assertHasNoErrors();

    $component
        ->call('editSetting', $apiKeySetting->id)
        ->set('editingValue', 'secret-api-key')
        ->call('updateAgentSetting')
        ->assertHasNoErrors();

    expect(Setting::query()->whereKey($baseUrlSetting->id)->value('value'))->toBe('https://api.example.com/v1');
    expect(Setting::query()->whereKey($apiKeySetting->id)->firstOrFail()->value)->toBe('secret-api-key');
    expect(Setting::query()->whereKey($apiKeySetting->id)->firstOrFail()->getRawOriginal('value'))->not->toBe('secret-api-key');
});

test('ai agent save button only appears after the value changes', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        'settings.site.ai-agent.view',
        'settings.site.ai-agent.update',
    ]);

    $this->actingAs($user);

    $setting = Setting::query()->firstOrCreate(
        ['key' => 'agent.transaction_file_checker.base_url'],
        [
            'group' => 'agent.transaction_file_checker',
            'value' => '',
            'type' => 'string',
            'label' => 'Base URL',
            'description' => 'Transaction file checker base URL.',
            'is_public' => false,
            'is_encrypted' => false,
            'autoload' => false,
            'sort_order' => 20,
        ],
    );

    Livewire::test(AiAgentSettings::class)
        ->call('editSetting', $setting->id)
        ->call('hasEditingChanges')
        ->assertReturned(false)
        ->set('editingValue', 'https://api.example.com/v1')
        ->call('hasEditingChanges')
        ->assertReturned(true);
});
