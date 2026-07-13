<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3 — Inserts system_settings keys for the Support Center module.
 * Idempotent: uses updateOrInsert so it is safe to re-run on production.
 * All keys are HungerStation-platform-scoped by convention (prefix: support_).
 */
class Phase3SupportSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ─── Attachments ──────────────────────────────────────────────────
            [
                'key'         => 'support_attachment_max_mb',
                'value'       => '10',
                'type'        => 'integer',
                'description' => 'الحد الأقصى لحجم المرفق الواحد في تذاكر الدعم (بالميجابايت)',
                'group'       => 'support',
                'is_public'   => 0,
            ],
            [
                'key'         => 'support_attachment_max_total_mb',
                'value'       => '30',
                'type'        => 'integer',
                'description' => 'الحد الأقصى لإجمالي حجم المرفقات لكل رد واحد (بالميجابايت)',
                'group'       => 'support',
                'is_public'   => 0,
            ],
            [
                'key'         => 'support_attachment_max_per_reply',
                'value'       => '5',
                'type'        => 'integer',
                'description' => 'الحد الأقصى لعدد المرفقات في رد واحد على تذكرة الدعم',
                'group'       => 'support',
                'is_public'   => 0,
            ],

            // ─── Ticket Limits ────────────────────────────────────────────────
            [
                'key'         => 'support_max_open_tickets_per_delegate',
                'value'       => '5',
                'type'        => 'integer',
                'description' => 'الحد الأقصى للتذاكر المفتوحة لكل مندوب في آنٍ واحد (مانع الإساءة)',
                'group'       => 'support',
                'is_public'   => 0,
            ],
            [
                'key'         => 'support_auto_close_resolved_days',
                'value'       => '7',
                'type'        => 'integer',
                'description' => 'عدد الأيام بعد انتهاء حالة "محلول" قبل إغلاق التذكرة تلقائياً',
                'group'       => 'support',
                'is_public'   => 0,
            ],

            // ─── SLA Grace Periods ────────────────────────────────────────────
            [
                'key'         => 'support_sla_grace_urgent_hours',
                'value'       => '1',
                'type'        => 'integer',
                'description' => 'ساعات الفترة السماحية بعد خرق SLA للتذاكر العاجلة قبل التصعيد',
                'group'       => 'support',
                'is_public'   => 0,
            ],
            [
                'key'         => 'support_sla_grace_high_hours',
                'value'       => '2',
                'type'        => 'integer',
                'description' => 'ساعات الفترة السماحية بعد خرق SLA للتذاكر ذات الأولوية العالية',
                'group'       => 'support',
                'is_public'   => 0,
            ],
            [
                'key'         => 'support_sla_grace_normal_hours',
                'value'       => '4',
                'type'        => 'integer',
                'description' => 'ساعات الفترة السماحية بعد خرق SLA للتذاكر العادية',
                'group'       => 'support',
                'is_public'   => 0,
            ],
            [
                'key'         => 'support_sla_grace_low_hours',
                'value'       => '8',
                'type'        => 'integer',
                'description' => 'ساعات الفترة السماحية بعد خرق SLA للتذاكر المنخفضة الأولوية',
                'group'       => 'support',
                'is_public'   => 0,
            ],

            // ─── Settlement Notifications ─────────────────────────────────────
            [
                'key'         => 'support_settlement_view_notify_admin',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'إرسال إشعار للإدارة عند أول مشاهدة مندوب لتسوية الشهر',
                'group'       => 'support',
                'is_public'   => 0,
            ],

            // ─── Portal ───────────────────────────────────────────────────────
            [
                'key'         => 'support_portal_enabled',
                'value'       => 'true',
                'type'        => 'boolean',
                'description' => 'تفعيل/تعطيل واجهة دعم المندوب في البوابة (سريع الإيقاف)',
                'group'       => 'support',
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
