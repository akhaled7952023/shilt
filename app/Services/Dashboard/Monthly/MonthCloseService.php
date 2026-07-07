<?php

namespace App\Services\Dashboard\Monthly;

use App\Enums\PeriodStatus;
use App\Models\Delegate;
use App\Models\MonthlyPeriod;
use App\Services\DelegateNotificationService;
use Illuminate\Support\Facades\DB;

class MonthCloseService
{
    public function __construct(
        protected DelegateNotificationService $notificationService,
    ) {}

    public function close(MonthlyPeriod $period, int $closedByUserId): void
    {
        if (! $period->isOpen()) {
            throw new \RuntimeException('لا يمكن إغلاق الفترة — الفترة ليست في حالة مفتوحة.');
        }

        DB::transaction(function () use ($period, $closedByUserId) {
            $period->status    = PeriodStatus::Closed;
            $period->closed_at = now();
            $period->closed_by = $closedByUserId;
            $period->save();
        });

        $this->notifyDelegates($period);
    }

    public function reopen(MonthlyPeriod $period): void
    {
        if (! $period->isClosed()) {
            throw new \RuntimeException('لا يمكن إعادة فتح الفترة — الفترة ليست في حالة مغلقة.');
        }

        DB::transaction(function () use ($period) {
            $period->status    = PeriodStatus::Open;
            $period->closed_at = null;
            $period->closed_by = null;
            $period->save();
        });
    }

    private function notifyDelegates(MonthlyPeriod $period): void
    {
        $period->loadMissing('platform');

        $table = match ($period->platform?->code) {
            'hungerstation' => 'hungerstation_ftr_settlements',
            'the-chefz'     => 'chefz_delegate_settlements',
            default         => null,
        };

        if (! $table) return;

        $delegateIds = DB::table($table)
            ->where('monthly_period_id', $period->id)
            ->pluck('delegate_id');

        if ($delegateIds->isEmpty()) return;

        Delegate::whereIn('id', $delegateIds)
            ->where('portal_enabled', true)
            ->get()
            ->each(fn($d) => $this->notificationService->notifySettlementPublished($d, $period));
    }
}
