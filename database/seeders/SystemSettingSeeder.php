<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ─── Financial ────────────────────────────────────────────────────
            [
                'key'         => 'chefz_vat_rate',
                'value'       => '0.1500',
                'type'        => 'decimal',
                'description' => 'نسبة ضريبة القيمة المضافة المطبقة على تسويات شيفز',
                'group'       => 'financial',
                'is_public'   => 0,
            ],
            [
                'key'         => 'chefz_commission_rate',
                'value'       => '0.2000',
                'type'        => 'decimal',
                'description' => 'نسبة عمولة الشركة المطبقة على صافي تسويات شيفز بعد الضريبة',
                'group'       => 'financial',
                'is_public'   => 0,
            ],

            // ─── Import ───────────────────────────────────────────────────────
            [
                'key'         => 'import_default_city_id',
                'value'       => '1',
                'type'        => 'integer',
                'description' => 'معرف المدينة الافتراضية لمنادب يتم إنشاؤهم تلقائياً عند الاستيراد',
                'group'       => 'import',
                'is_public'   => 0,
            ],
            [
                'key'         => 'import_default_bank_name',
                'value'       => 'Unknown',
                'type'        => 'string',
                'description' => 'اسم البنك الافتراضي للمناديب الجدد المنشأين تلقائياً',
                'group'       => 'import',
                'is_public'   => 0,
            ],
            [
                'key'         => 'import_default_iban',
                'value'       => '000000000000000000',
                'type'        => 'string',
                'description' => 'رقم الآيبان الافتراضي للمناديب الجدد المنشأين تلقائياً',
                'group'       => 'import',
                'is_public'   => 0,
            ],
            [
                'key'         => 'import_default_status',
                'value'       => 'active',
                'type'        => 'string',
                'description' => 'حالة المندوب الافتراضية عند إنشائه تلقائياً (active أو inactive)',
                'group'       => 'import',
                'is_public'   => 0,
            ],
            [
                'key'         => 'import_max_file_mb',
                'value'       => '20',
                'type'        => 'integer',
                'description' => 'الحد الأقصى لحجم ملف الاستيراد بالميجابايت',
                'group'       => 'import',
                'is_public'   => 0,
            ],

            // ─── Company ──────────────────────────────────────────────────────
            [
                'key'         => 'company_name_ar',
                'value'       => 'شيلت للخدمات اللوجستية',
                'type'        => 'string',
                'description' => 'اسم الشركة بالعربية — يظهر في الترويسات والتقارير',
                'group'       => 'company',
                'is_public'   => 1,
            ],
            [
                'key'         => 'company_name_en',
                'value'       => 'SHILT Logistics Services',
                'type'        => 'string',
                'description' => 'اسم الشركة بالإنجليزية',
                'group'       => 'company',
                'is_public'   => 1,
            ],
            [
                'key'         => 'company_logo_path',
                'value'       => '',
                'type'        => 'string',
                'description' => 'مسار شعار الشركة المستخدم في التقارير والبوابة (رفع ملف)',
                'group'       => 'company',
                'is_public'   => 1,
            ],
            [
                'key'         => 'company_address',
                'value'       => '—',
                'type'        => 'string',
                'description' => 'عنوان الشركة — يظهر في التقارير',
                'group'       => 'company',
                'is_public'   => 1,
            ],
            [
                'key'         => 'company_phone',
                'value'       => '—',
                'type'        => 'string',
                'description' => 'رقم هاتف الشركة — يظهر في التقارير',
                'group'       => 'company',
                'is_public'   => 1,
            ],
            [
                'key'         => 'company_cr',
                'value'       => '—',
                'type'        => 'string',
                'description' => 'رقم السجل التجاري — يظهر في التقارير',
                'group'       => 'company',
                'is_public'   => 1,
            ],
            [
                'key'         => 'notification_admin_email',
                'value'       => '',
                'type'        => 'string',
                'description' => 'البريد الإلكتروني للمشرف الذي يستقبل إشعارات النظام (تذاكر، تسويات، منتهيات الصلاحية، ...)',
                'group'       => 'company',
                'is_public'   => 0,
            ],
            [
                'key'         => 'report_header_ar',
                'value'       => 'تقرير المندوب',
                'type'        => 'string',
                'description' => 'عنوان التقرير الظاهر في الترويسة العربية',
                'group'       => 'company',
                'is_public'   => 0,
            ],
            [
                'key'         => 'report_show_iqama',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'عرض رقم الإقامة في تقرير التسوية (true/false)',
                'group'       => 'company',
                'is_public'   => 0,
            ],
            [
                'key'         => 'report_show_platform_id',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'عرض معرف المنصة في تقرير التسوية (true/false)',
                'group'       => 'company',
                'is_public'   => 0,
            ],

            // ─── Portal ───────────────────────────────────────────────────────
            [
                'key'         => 'portal_session_lifetime',
                'value'       => '120',
                'type'        => 'integer',
                'description' => 'مدة صلاحية جلسة بوابة المندوب بالدقائق',
                'group'       => 'portal',
                'is_public'   => 0,
            ],
            [
                'key'         => 'portal_max_login_attempts',
                'value'       => '5',
                'type'        => 'integer',
                'description' => 'الحد الأقصى لمحاولات تسجيل الدخول الفاشلة قبل قفل الحساب',
                'group'       => 'portal',
                'is_public'   => 0,
            ],
            [
                'key'         => 'portal_lockout_minutes',
                'value'       => '30',
                'type'        => 'integer',
                'description' => 'مدة قفل حساب البوابة بعد تجاوز الحد الأقصى للمحاولات (بالدقائق)',
                'group'       => 'portal',
                'is_public'   => 0,
            ],

            // ─── Notifications ────────────────────────────────────────────────
            [
                'key'         => 'notify_document_expiry_days',
                'value'       => '30,14,7',
                'type'        => 'string',
                'description' => 'أيام التنبيه قبل انتهاء الوثائق، مفصولة بفاصلة (مثال: 30,14,7)',
                'group'       => 'notifications',
                'is_public'   => 0,
            ],
            [
                'key'         => 'notify_leave_days_ahead',
                'value'       => '3',
                'type'        => 'integer',
                'description' => 'عدد الأيام المسبقة لإرسال تنبيه بداية إجازة المندوب',
                'group'       => 'notifications',
                'is_public'   => 0,
            ],
            [
                'key'         => 'notify_open_period_day',
                'value'       => '25',
                'type'        => 'integer',
                'description' => 'يوم الشهر الذي يُرسل فيه تذكير بفترات الشهر المفتوحة',
                'group'       => 'notifications',
                'is_public'   => 0,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
