<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolePermissionSeeder::class,
            RoleSeeder::class,
            PersonnelRosterSeeder::class,
            SettingSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            ProgramSeeder::class,
            AcademicYearSeeder::class,
            AcademicCourseSeeder::class,
            AcademicEnrollmentSeeder::class,
            RegulationSeeder::class,
            UserDetailSeeder::class,
            UserParentSeeder::class,
        ]);
    }
}
