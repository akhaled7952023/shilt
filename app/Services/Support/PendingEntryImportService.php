<?php

namespace App\Services\Support;

use App\Enums\PendingEntryStatus;
use App\Models\HungerStationFtrDelegateDeduction;
use App\Models\HungerStationFtrSettlement;
use App\Models\MonthlyPeriod;
use App\Models\PendingFinancialEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PendingEntryImportService
{
    /**
     * Read-only preview for the given period.
     * No database writes — safe to call on every page load.
     *
     * @return array{total:int, importable:int, skipped:int, total_amount:float, entries:\Illuminate\Support\Collection}
     */
    public function preview(MonthlyPeriod $period): array
    {
        $empty = ['total' => 0, 'importable' => 0, 'skipped' => 0, 'total_amount' => 0.0, 'entries' => collect()];

        $entries = PendingFinancialEntry::where('platform', 'hungerstation')
            ->where('status', PendingEntryStatus::Pending->value)
            ->where('settlement_month', $period->month)
            ->where('settlement_year', $period->year)
            ->with('delegate')
            ->orderBy('delegate_id')
            ->get();

        if ($entries->isEmpty()) {
            return $empty;
        }

        $delegateIds = $entries->pluck('delegate_id')->unique()->values();
        $settlements = HungerStationFtrSettlement::where('monthly_period_id', $period->id)
            ->whereIn('delegate_id', $delegateIds)
            ->get()
            ->keyBy('delegate_id');

        $enriched = $entries->map(function (PendingFinancialEntry $entry) use ($settlements) {
            $settlement = $settlements->get($entry->delegate_id);
            $skipReason = null;

            if (! $settlement) {
                $skipReason = 'لا يوجد تسوية لهذا المندوب في هذه الفترة';
            } elseif ($settlement->is_locked) {
                $skipReason = 'التسوية مقفلة';
            }

            return (object) [
                'entry'       => $entry,
                'delegate'    => $entry->delegate,
                'settlement'  => $settlement,
                'skip_reason' => $skipReason,
                'will_import' => $skipReason === null,
            ];
        });

        $importable = $enriched->filter(fn ($r) => $r->will_import)->count();

        return [
            'total'        => $entries->count(),
            'importable'   => $importable,
            'skipped'      => $entries->count() - $importable,
            'total_amount' => (float) $enriched->filter(fn ($r) => $r->will_import)->sum(fn ($r) => (float) $r->entry->amount),
            'entries'      => $enriched,
        ];
    }

    /**
     * Import pending entries for the given period into settlements.
     * Entries are grouped by (delegate, deduction_type, is_benefit) — one adjustment per group.
     * Runs inside one DB transaction — full rollback on any failure.
     *
     * @return array{imported:int, skipped:int, reasons:array<string>}
     * @throws \Throwable
     */
    public function apply(MonthlyPeriod $period, User $admin): array
    {
        return DB::transaction(function () use ($period, $admin) {
            // Lock rows to prevent concurrent double-import
            $entries = PendingFinancialEntry::where('platform', 'hungerstation')
                ->where('status', PendingEntryStatus::Pending->value)
                ->where('settlement_month', $period->month)
                ->where('settlement_year', $period->year)
                ->lockForUpdate()
                ->get();

            if ($entries->isEmpty()) {
                return ['imported' => 0, 'skipped' => 0, 'reasons' => ['لا توجد قيود معلقة لهذه الفترة.']];
            }

            $delegateIds = $entries->pluck('delegate_id')->unique()->values();
            $settlements = HungerStationFtrSettlement::where('monthly_period_id', $period->id)
                ->whereIn('delegate_id', $delegateIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('delegate_id');

            $imported      = 0;
            $skipped       = 0;
            $reasonBuckets = [];
            $importable    = collect();

            // ── Pass 1: classify each entry as importable or skipped ──────────
            foreach ($entries as $entry) {
                // Re-check status inside transaction (concurrent guard)
                if ($entry->status !== PendingEntryStatus::Pending) {
                    $skipped++;
                    $reasonBuckets['تم استيراده مسبقاً'] = ($reasonBuckets['تم استيراده مسبقاً'] ?? 0) + 1;
                    continue;
                }

                $settlement = $settlements->get($entry->delegate_id);

                if (! $settlement) {
                    $skipped++;
                    $reasonBuckets['لا تسوية للمندوب في هذه الفترة'] = ($reasonBuckets['لا تسوية للمندوب في هذه الفترة'] ?? 0) + 1;
                    continue;
                }

                if ($settlement->is_locked) {
                    $skipped++;
                    $reasonBuckets['التسوية مقفلة'] = ($reasonBuckets['التسوية مقفلة'] ?? 0) + 1;
                    continue;
                }

                $importable->push($entry);
            }

            // ── Pass 2: group by (delegate, type, is_benefit) → one adjustment per group ──
            $groups = $importable->groupBy(function (PendingFinancialEntry $e) {
                return $e->delegate_id . '|' . $e->deduction_type . '|' . ((int) $e->is_benefit);
            });

            // Track settlements that need recalculation (keyed by settlement_id, deduplicated)
            $settlementsToRecalculate = collect();

            foreach ($groups as $groupEntries) {
                $first       = $groupEntries->first();
                $settlement  = $settlements->get($first->delegate_id);
                $totalAmount = $groupEntries->sum(fn (PendingFinancialEntry $e) => (float) $e->amount);
                $count       = $groupEntries->count();

                if ($count === 1) {
                    $notes = "[طلب #{$first->financial_request_id}] " . ($first->notes ?? '');
                } else {
                    $ids   = $groupEntries
                        ->pluck('financial_request_id')
                        ->filter()
                        ->map(fn ($id) => "#{$id}")
                        ->join('، ');
                    $notes = "طلبات مالية معتمدة: {$count} ({$ids})";
                }

                // One adjustment row for the entire group
                $deduction = HungerStationFtrDelegateDeduction::create([
                    'settlement_id'     => $settlement->id,
                    'monthly_period_id' => $period->id,
                    'delegate_id'       => $first->delegate_id,
                    'deduction_type'    => $first->deduction_type,
                    'is_benefit'        => $first->is_benefit,
                    'label'             => $first->label,
                    'amount'            => $totalAmount,
                    'notes'             => $notes,
                    'created_by'        => $admin->id,
                ]);

                // Mark every entry in the group imported; all share the same adjustment_id
                foreach ($groupEntries as $entry) {
                    $entry->status        = PendingEntryStatus::Imported;
                    $entry->settlement_id = $settlement->id;
                    $entry->adjustment_id = $deduction->id;
                    $entry->imported_at   = now();
                    $entry->imported_by   = $admin->id;
                    $entry->save();
                }

                $imported += $count;
                $settlementsToRecalculate->put($settlement->id, $settlement);
            }

            // ── Pass 3: recalculate each affected settlement exactly once ─────
            foreach ($settlementsToRecalculate as $settlement) {
                $settlement->recalculate();
                $settlement->updated_by = $admin->id;
                $settlement->save();
            }

            $reasons = [];
            foreach ($reasonBuckets as $reason => $count) {
                $reasons[] = "{$reason} ({$count})";
            }

            return ['imported' => $imported, 'skipped' => $skipped, 'reasons' => $reasons];
        });
    }
}
