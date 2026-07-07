<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name'              => 'HungerStation',
                'code'              => 'hungerstation',
                'min_km_threshold'  => 450.00,
                'penalty_per_km'    => 0.50,
                'is_active'         => true,
            ],
            [
                'name'              => 'The Chefz',
                'code'              => 'the-chefz',
                'min_km_threshold'  => 450.00,
                'penalty_per_km'    => 0.50,
                'is_active'         => true,
            ],
        ];

        foreach ($platforms as $platform) {
            Platform::create($platform);
        }
    }
}
