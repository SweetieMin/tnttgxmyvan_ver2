<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'christian_name' => 'Giuse',
                'last_name' => 'Đặng Đình',
                'name' => 'Viên',
                'birthday' => '2010-08-21',
                'username' => 'MV21081010',
                'email' => 'tntt.myvan@gmail.com',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Toma',
                'last_name' => 'Nguyễn Khắc',
                'name' => 'Huấn',
                'username' => 'MV19019797',
                'birthday' => '1997-01-19',
                'email' => 'nguyenkhachuan1997@gmail.com',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Toma',
                'last_name' => 'Vũ Minh',
                'name' => 'Đức',
                'username' => 'MV01109999',
                'birthday' => '1999-10-01',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Teresa',
                'last_name' => 'Nguyễn Thị Thúy',
                'name' => 'Vy',
                'username' => 'MV11100101',
                'birthday' => '2001-10-11',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Teresa',
                'last_name' => 'Nguyễn Thị Ngọc',
                'name' => 'Vân',
                'username' => 'MV19089999',
                'birthday' => '1999-08-19',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Nguyễn Thị Bích',
                'name' => 'Liên',
                'username' => 'MV22109686',
                'birthday' => '1996-10-22',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Đoàn Trường',
                'name' => 'Nam',
                'username' => 'MV21099898',
                'birthday' => '1998-09-21',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Maria Monica',
                'last_name' => 'Nguyễn Thị Kim',
                'name' => 'Anh',
                'username' => 'MV26089924',
                'birthday' => '1999-08-26',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Vũ Hồng',
                'name' => 'Phúc',
                'username' => 'MV03010574',
                'birthday' => '2005-01-03',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Monica',
                'last_name' => 'Nguyễn Hoàng Kim',
                'name' => 'Dung',
                'username' => 'MV26030101',
                'birthday' => '2001-03-26',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Toma',
                'last_name' => 'Vũ Tấn',
                'name' => 'Lộc',
                'username' => 'MV01070274',
                'birthday' => '2002-07-01',
                'password' => '12345',
                'token' => Str::random(60),
            ],
            [
                'christian_name' => 'Maria',
                'last_name' => 'Phạm Ngọc',
                'name' => 'Quỳnh',
                'username' => 'MV14090524',
                'birthday' => '2005-09-14',
                'password' => '12345',
                'token' => Str::random(60),
            ],
        ];

        foreach ($users as $user) {
            $existingUser = User::withTrashed()
                ->where('username', $user['username'])
                ->orWhere('email', $user['email'] ?? null)
                ->first();

            $user['token'] = $existingUser?->token ?? $user['token'];

            User::updateOrCreate(
                ['username' => $user['username']],
                array_merge($user, ['deleted_at' => null]),
            );
        }
    }
}
