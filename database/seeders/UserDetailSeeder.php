<?php

namespace Database\Seeders;

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
                'user_id' => 2,
                'picture' => 'MV19019797-681c7d271bf67.png',
                'bio' => '',
                'phone'=> '',
                'address'=> '',
                'gender'=> 'male',
            ],
        ];

        foreach ($details as $detail){
            UserDetail::create($detail);
        }
    }
}
