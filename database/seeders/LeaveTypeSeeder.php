<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['ar' => 'إجازة سنوية', 'en' => 'Annual Leave'],
            ['ar' => 'إجازة مرضية', 'en' => 'Sick Leave'],
            ['ar' => 'إجازة بدون راتب', 'en' => 'Unpaid Leave'],
            ['ar' => 'عارض', 'en' => 'Emergency Leave'],
        ];

        foreach ($types as $type) {
            LeaveType::create([
                'name'      => ['ar' => $type['ar'], 'en' => $type['en']],
                'is_active' => true,
            ]);
        }
    }
}
