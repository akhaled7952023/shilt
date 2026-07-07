<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['ar' => 'دراجة نارية', 'en' => 'Motorcycle'],
            ['ar' => 'سيارة', 'en' => 'Car'],
            ['ar' => 'دراجة هوائية', 'en' => 'Bicycle'],
        ];

        foreach ($types as $type) {
            VehicleType::create([
                'name'      => ['ar' => $type['ar'], 'en' => $type['en']],
                'is_active' => true,
            ]);
        }
    }
}
