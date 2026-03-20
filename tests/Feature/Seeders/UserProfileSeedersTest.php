<?php

use App\Models\User;
use Database\Seeders\UserDetailSeeder;
use Database\Seeders\UserParentSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user detail and parent seeders create profile records for seeded users', function () {
    $this->seed(UserSeeder::class);
    $this->seed(UserDetailSeeder::class);
    $this->seed(UserParentSeeder::class);

    $adminUser = User::query()
        ->where('username', 'MV21081010')
        ->firstOrFail();

    $leaderUser = User::query()
        ->where('username', 'MV19019797')
        ->firstOrFail();

    expect($adminUser->details)->not->toBeNull();
    expect($adminUser->parents)->not->toBeNull();
    expect($leaderUser->details)->not->toBeNull();
    expect($leaderUser->parents)->not->toBeNull();
    expect($leaderUser->details?->phone)->toBe('0901000001');
    expect($adminUser->parents?->name_father)->toBe('Dang Van Mau');
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
