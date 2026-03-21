<?php

use App\Livewire\Admin\Settings\Log\ActivityFailedSystem;
use App\Models\ActivityFailedLog;
use App\Models\Permission;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    Permission::findOrCreate('settings.log.activity-failed.view', 'web');
});

test('authorized users can visit the activity failed system page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.log.activity-failed.view');

    ActivityFailedLog::record(
        logName: 'roles',
        action: 'create',
        subject: $user,
        properties: ['payload' => ['name' => 'Manager']],
        message: 'Create role failed.',
    );

    $response = $this->actingAs($user)->get(route('admin.settings.log.activity-failed'));

    $response->assertOk()
        ->assertSeeText(__('Failed activity logs'))
        ->assertSeeText(__('Review failed database actions and persistence errors'));
});

test('activity failed detail can be opened from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.log.activity-failed.view');

    $activityFailedLog = ActivityFailedLog::record(
        logName: 'permissions',
        action: 'delete',
        subject: $user,
        properties: [
            'attributes' => ['name' => 'access.post.delete'],
        ],
        message: 'Delete permission failed.',
    );

    $this->actingAs($user);

    Livewire::test(ActivityFailedSystem::class)
        ->call('loadActivities')
        ->call('openDetail', $activityFailedLog->id)
        ->assertSet('selectedActivityFailedLog.id', $activityFailedLog->id)
        ->assertSee(__('Failed activity log details'))
        ->assertSee('access.post.delete');
});
