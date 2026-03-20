<?php

use App\Models\User;
use App\Models\UserDetail;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('authenticated users can visit the dashboard when they have a detail record', function () {
    $user = User::factory()->create();

    UserDetail::query()->create([
        'user_id' => $user->id,
        'picture' => 'avatar.png',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));

    $response->assertOk();
});
