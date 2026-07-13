<?php

namespace App\Services\Portal;

use App\Enums\NotificationCategory;
use App\Models\HungerStationFtrSettlement;
use App\Models\MonthlyPeriod;
use App\Models\SettlementView;
use App\Services\Support\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * P3-005 / P4-008 — Settlement first-view tracking for HungerStation.
 *
 * Records every view (idempotent — safe to call on each page load).
 * On the delegate's FIRST view, notifies all admins via a portal notification.
 * Failures are logged and never propagate to the caller.
 */
class SettlementViewTracker
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Record a delegate viewing their HungerStation settlement.
     * Call this inside DelegateSettlementController::show() — wrapped in try/catch.
     */
    public function record(
        HungerStationFtrSettlement $settlement,
        $delegate,
        MonthlyPeriod $period,
    ): void {
        try {
            $view = SettlementView::firstOrCreate(
                [
                    'settlement_id' => $settlement->id,
                    'delegate_id'   => $delegate->id,
                ],
                [
                    'platform'          => 'hungerstation',
                    'monthly_period_id' => $settlement->monthly_period_id,
                    'first_viewed_at'   => now(),
                    'last_viewed_at'    => now(),
                    'view_count'        => 1,
                    'notification_sent' => false,
                ]
            );

            if ($view->wasRecentlyCreated) {
                // First view — notify all admins once
                $this->notifyAdmins($settlement, $delegate, $period);
                $view->notification_sent = true;
                $view->save();
            } else {
                // Subsequent view — increment counter only
                DB::table('settlement_views')
                    ->where('id', $view->id)
                    ->update([
                        'last_viewed_at' => now(),
                        'view_count'     => DB::raw('view_count + 1'),
                    ]);
            }
        } catch (\Throwable $e) {
            Log::warning('SettlementViewTracker: failed to record view', [
                'settlement_id' => $settlement->id,
                'delegate_id'   => $delegate->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    private function notifyAdmins(
        HungerStationFtrSettlement $settlement,
        $delegate,
        MonthlyPeriod $period,
    ): void {
        $periodLabel = $period->getDisplayLabel();

        $this->notificationService->sendToAllAdmins(
            category:       NotificationCategory::SettlementViewed,
            title:          "اطّلع {$delegate->name} على كشفه — {$periodLabel}",
            body:           "المندوب {$delegate->name} فتح كشفه الشهري لفترة {$periodLabel} للمرة الأولى.",
            actionUrl:      route('dashboard.monthly.periods.hungerstation.ftr.settlement.index', $period->id),
            notifiableType: HungerStationFtrSettlement::class,
            notifiableId:   $settlement->id,
        );
    }
}
