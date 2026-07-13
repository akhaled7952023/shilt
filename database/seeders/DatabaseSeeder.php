<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CitySeeder::class,
            PlatformSeeder::class,
            VehicleTypeSeeder::class,
            DocumentTypeSeeder::class,
            WarningTypeSeeder::class,
            LeaveTypeSeeder::class,
            SystemSettingSeeder::class,
            SlaPolicySeeder::class,
            Phase3SupportSettingSeeder::class,
        ]);
    }
}
