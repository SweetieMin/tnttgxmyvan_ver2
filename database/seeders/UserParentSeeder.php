<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserParent;
use Illuminate\Database\Seeder;

class UserParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parents = [
            [
                'username' => 'MV21081010',
                'christian_name_father' => 'Giuse',
                'name_father' => 'Dang Van Mau',
                'phone_father' => '0902000001',
                'christian_name_mother' => 'Maria',
                'name_mother' => 'Tran Thi Hien',
                'phone_mother' => '0902000002',
                'christian_name_god_parent' => 'Phaolo',
                'name_god_parent' => 'Le Van Binh',
                'phone_god_parent' => '0902000003',
            ],
            [
                'username' => 'MV19019797',
                'christian_name_father' => 'Phanxico',
                'name_father' => 'Nguyen Khac Cuong',
                'phone_father' => '0902000011',
                'christian_name_mother' => 'Anna',
                'name_mother' => 'Pham Thi Hoa',
                'phone_mother' => '0902000012',
                'christian_name_god_parent' => 'Gioan',
                'name_god_parent' => 'Bui Van Son',
                'phone_god_parent' => '0902000013',
            ],
        ];

        foreach ($parents as $parent) {
            $user = User::query()
                ->where('username', $parent['username'])
                ->first();

            if (! $user) {
                continue;
            }

            UserParent::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'christian_name_father' => $parent['christian_name_father'],
                    'name_father' => $parent['name_father'],
                    'phone_father' => $parent['phone_father'],
                    'christian_name_mother' => $parent['christian_name_mother'],
                    'name_mother' => $parent['name_mother'],
                    'phone_mother' => $parent['phone_mother'],
                    'christian_name_god_parent' => $parent['christian_name_god_parent'],
                    'name_god_parent' => $parent['name_god_parent'],
                    'phone_god_parent' => $parent['phone_god_parent'],
                ],
            );
        }
    }
}
