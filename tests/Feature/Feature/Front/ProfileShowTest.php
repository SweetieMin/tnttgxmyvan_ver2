<?php

use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserParent;

test('public profile page is accessible by token without authentication', function () {
    $user = User::factory()->create([
        'token' => str_repeat('a', 64),
        'christian_name' => 'Maria Monica',
        'last_name' => 'Nguyễn Thị',
        'name' => 'Kim Anh',
        'birthday' => '2010-08-21',
    ]);

    UserDetail::query()->create([
        'user_id' => $user->id,
        'phone' => '0901000001',
        'address' => 'Giáo xứ Mỹ Vân, Nam Định',
        'gender' => 'female',
    ]);

    UserParent::query()->create([
        'user_id' => $user->id,
        'christian_name_father' => 'Giuse',
        'name_father' => 'Nguyễn Văn A',
        'phone_father' => '0901000111',
        'christian_name_mother' => 'Anna',
        'name_mother' => 'Trần Thị B',
        'phone_mother' => '0912000222',
    ]);

    $response = $this->get('/profile/'.str_repeat('a', 64));

    $response->assertOk()
        ->assertSeeText('Nguyễn Thị Kim Anh')
        ->assertSeeText('Maria Monica')
        ->assertSeeText(__('Personal information'))
        ->assertSeeText(__('Parents information'))
        ->assertSeeText('090******001')
        ->assertSeeText('090******111')
        ->assertSeeText('091******222');
});

test('public profile page returns 404 for an unknown token', function () {
    $this->get('/profile/'.str_repeat('z', 64))
        ->assertNotFound();
});
