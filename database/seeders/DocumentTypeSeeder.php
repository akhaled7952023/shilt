<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['ar' => 'رخصة قيادة', 'en' => 'Driving License', 'applies_to' => 'delegate', 'is_required' => true],
            ['ar' => 'إقامة', 'en' => 'Iqama', 'applies_to' => 'delegate', 'is_required' => true],
            ['ar' => 'بطاقة وطنية', 'en' => 'National ID', 'applies_to' => 'delegate', 'is_required' => true],
            ['ar' => 'تأمين طبي', 'en' => 'Medical Insurance', 'applies_to' => 'delegate', 'is_required' => false],
            ['ar' => 'استمارة', 'en' => 'Vehicle Registration', 'applies_to' => 'vehicle', 'is_required' => true],
            ['ar' => 'تأمين', 'en' => 'Insurance', 'applies_to' => 'vehicle', 'is_required' => true],
            ['ar' => 'فحص دوري', 'en' => 'Periodic Inspection', 'applies_to' => 'vehicle', 'is_required' => false],
        ];

        foreach ($types as $type) {
            DocumentType::create([
                'name'        => ['ar' => $type['ar'], 'en' => $type['en']],
                'applies_to'  => $type['applies_to'],
                'is_required' => $type['is_required'],
                'is_active'   => true,
            ]);
        }
    }
}
