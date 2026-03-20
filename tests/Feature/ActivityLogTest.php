<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserParent;
use App\Models\UserReligiousProfile;
use Spatie\Activitylog\Models\Activity;

test('all models record activity for create update and delete events', function () {
    $user = User::factory()->create();

    $user->update([
        'name' => 'Updated Name',
    ]);
    $user->delete();

    $detail = UserDetail::create([
        'user_id' => $user->id,
        'bio' => 'Initial bio',
        'phone' => '0123456789',
        'address' => 'My Van',
        'gender' => 'male',
    ]);

    $detail->update([
        'bio' => 'Updated bio',
    ]);
    $detail->delete();

    $parent = UserParent::create([
        'user_id' => $user->id,
        'name_father' => 'Father Name',
        'name_mother' => 'Mother Name',
    ]);

    $parent->update([
        'name_father' => 'Updated Father Name',
    ]);
    $parent->delete();

    $religiousProfile = UserReligiousProfile::create([
        'user_id' => $user->id,
        'baptism_place' => 'Parish A',
        'status_religious' => 'in_course',
        'is_attendance' => true,
    ]);

    $religiousProfile->update([
        'baptism_place' => 'Parish B',
    ]);
    $religiousProfile->delete();

    $role = Role::create([
        'name' => 'Activity Role',
        'guard_name' => 'web',
    ]);

    $role->update([
        'name' => 'Updated Activity Role',
    ]);
    $role->delete();

    $permission = Permission::create([
        'name' => 'activity.permission.view',
        'guard_name' => 'web',
    ]);

    $permission->update([
        'name' => 'activity.permission.manage',
    ]);
    $permission->delete();

    expect(Activity::query()->where('subject_type', User::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);

    expect(Activity::query()->where('subject_type', UserDetail::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);

    expect(Activity::query()->where('subject_type', UserParent::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);

    expect(Activity::query()->where('subject_type', UserReligiousProfile::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);

    expect(Activity::query()->where('subject_type', Role::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);

    expect(Activity::query()->where('subject_type', Permission::class)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted']);
});

test('user activity log excludes hidden attributes', function () {
    $user = User::factory()->create([
        'password' => 'password',
        'token' => 'secret-token',
    ]);

    $activity = Activity::query()
        ->where('subject_type', User::class)
        ->where('subject_id', $user->id)
        ->where('event', 'created')
        ->firstOrFail();

    expect($activity->properties['attributes'])
        ->not->toHaveKeys(['password', 'token', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']);
});
