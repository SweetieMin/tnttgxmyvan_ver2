<?php

use App\Livewire\Admin\Settings\Log\ActivitySystem;
use App\Models\Permission;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    Permission::findOrCreate('settings.log.activity.view', 'web');
});

test('authorized users can visit the activity system page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.log.activity.view');

    activity()
        ->causedBy($user)
        ->performedOn($user)
        ->event('updated')
        ->log('User profile updated');

    $response = $this->actingAs($user)->get(route('admin.settings.log.activity'));

    $response->assertOk()
        ->assertSeeText(__('Activity system'))
        ->assertSeeText(__('Manage activity logs and system events'));
});

test('activity detail can be opened from the livewire screen', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('settings.log.activity.view');

    $activity = activity()
        ->causedBy($user)
        ->performedOn($user)
        ->withProperties([
            'attributes' => ['name' => 'Updated User'],
        ])
        ->event('updated')
        ->log('User updated');

    $this->actingAs($user);

    Livewire::test(ActivitySystem::class)
        ->call('loadActivities')
        ->call('openDetail', $activity->id)
        ->assertSet('selectedActivity.id', $activity->id)
        ->assertSee(__('Activity log details'))
        ->assertSee('Updated User');

    expect(Activity::query()->find($activity->id))->not->toBeNull();
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
