<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Seeder;

class UserDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $details = [
            [
                'username' => 'MV19019797',
                'picture' => null,
                'bio' => 'Huynh truong phuc vu tai xu doan Thieu Nhi Thanh The giao xu My Van.',
                'phone' => '0901000001',
                'address' => 'Giao xu My Van, Nam Dinh',
                'gender' => 'male',
            ],
            [
                'username' => 'MV21081010',
                'picture' => 'MV21081010-681c7d271bf67.png',
                'bio' => 'Thieu nhi tham gia sinh hoat tai giao xu My Van.',
                'phone' => '0901000002',
                'address' => 'Giao xu My Van, Nam Dinh',
                'gender' => 'male',
            ],
        ];

        foreach ($details as $detail) {
            $user = User::query()
                ->where('username', $detail['username'])
                ->first();

            if (! $user) {
                continue;
            }

            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'picture' => $detail['picture'],
                    'bio' => $detail['bio'],
                    'phone' => $detail['phone'],
                    'address' => $detail['address'],
                    'gender' => $detail['gender'],
                ],
            );
        }
    }
}
