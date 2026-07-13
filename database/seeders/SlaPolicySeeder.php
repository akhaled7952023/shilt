<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3 — Seeds the 4 SLA policy rows (one per priority level).
 * Idempotent: uses updateOrInsert keyed on priority so it is safe to re-run.
 * Values are conservative defaults; super_admin may edit them via System Settings.
 *
 * first_response_hours: deadline for first admin reply after ticket opens
 * resolution_hours: deadline for full resolution from ticket open
 */
class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'priority'              => 'urgent',
                'first_response_hours'  => 2,
                'resolution_hours'      => 8,
            ],
            [
                'priority'              => 'high',
                'first_response_hours'  => 4,
                'resolution_hours'      => 24,
            ],
            [
                'priority'              => 'normal',
                'first_response_hours'  => 8,
                'resolution_hours'      => 48,
            ],
            [
                'priority'              => 'low',
                'first_response_hours'  => 24,
                'resolution_hours'      => 96,
            ],
        ];

        foreach ($policies as $policy) {
            DB::table('sla_policies')->updateOrInsert(
                ['priority' => $policy['priority']],
                array_merge($policy, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }
    }
}
