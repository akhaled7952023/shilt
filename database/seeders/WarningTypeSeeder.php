<?php

namespace Database\Seeders;

use App\Models\WarningType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class WarningTypeSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        WarningType::truncate();
        Schema::enableForeignKeyConstraints();

        $types = [
            // Traffic / road violations
            ['ar' => 'استخدام الهاتف أثناء القيادة',  'en' => 'Using Phone While Driving'],
            ['ar' => 'تجاوز السرعة',                   'en' => 'Speeding'],
            ['ar' => 'تجاوز الإشارة الحمراء',          'en' => 'Running a Red Light'],
            ['ar' => 'مخالفة حزام الأمان',             'en' => 'Seatbelt Violation'],
            ['ar' => 'الوقوف في مكان ممنوع',           'en' => 'Parking in Prohibited Area'],
            ['ar' => 'القيادة بعكس الاتجاه',           'en' => 'Wrong-Way Driving'],
            ['ar' => 'الانشغال عن الطريق',             'en' => 'Distracted Driving'],

            // Safety violations
            ['ar' => 'مخالفة تعليمات السلامة',         'en' => 'Safety Instruction Violation'],
            ['ar' => 'إهمال المركبة',                   'en' => 'Vehicle Negligence'],
            ['ar' => 'التسبب في حادث',                  'en' => 'Causing an Accident'],

            // Order / service violations
            ['ar' => 'التأخر عن استلام الطلب',         'en' => 'Late Order Pickup'],
            ['ar' => 'التأخر عن تسليم الطلب',          'en' => 'Late Order Delivery'],
            ['ar' => 'إلغاء الطلب بدون سبب',           'en' => 'Order Cancellation Without Reason'],
            ['ar' => 'سوء التعامل مع العميل',           'en' => 'Poor Customer Treatment'],

            // Conduct / policy violations
            ['ar' => 'عدم الالتزام بالزي الرسمي',      'en' => 'Uniform Non-Compliance'],
            ['ar' => 'مخالفة تعليمات المنصة',           'en' => 'Platform Instruction Violation'],
            ['ar' => 'غياب بدون إشعار',                 'en' => 'Absence Without Notice'],
            ['ar' => 'مخالفة تعليمات الشركة',           'en' => 'Company Instruction Violation'],
            ['ar' => 'مخالفة تشغيلية أخرى',            'en' => 'Other Operational Violation'],
        ];

        foreach ($types as $type) {
            WarningType::create([
                'name'      => ['ar' => $type['ar'], 'en' => $type['en']],
                'is_active' => true,
            ]);
        }
    }
}
