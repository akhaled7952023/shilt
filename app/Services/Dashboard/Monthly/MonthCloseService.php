<?php

namespace App\Services\Dashboard\Monthly;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use App\Enums\PeriodStatus;
use App\Models\Delegate;
use App\Models\MonthlyPeriod;
use App\Services\DelegateNotificationService;
use App\Services\Support\EmailNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonthCloseService
{
    public function __construct(
        protected DelegateNotificationService $notificationService,
        protected EmailNotificationService    $emailService,
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

        $delegates = Delegate::whereIn('id', $delegateIds)
            ->where('portal_enabled', true)
            ->get();

        // Existing system: send old DelegateNotification
        $delegates->each(fn ($d) => $this->notificationService->notifySettlementPublished($d, $period));

        // P4-009: also write to the new Phase 3 notifications table (HungerStation only)
        if ($period->platform?->code === 'hungerstation' && $delegates->isNotEmpty()) {
            try {
                $periodLabel = $period->getDisplayLabel();
                $now         = now()->toDateTimeString();

                $rows = $delegates->map(fn ($d) => [
                    'recipient_type'  => 'delegate',
                    'recipient_id'    => $d->id,
                    'channel'         => NotificationChannel::Portal->value,
                    'category'        => NotificationCategory::SettlementPublished->value,
                    'title'           => "كشف راتبك لشهر {$periodLabel} متاح الآن",
                    'body'            => "يمكنك الآن مراجعة تفاصيل كشف راتبك الشهري لفترة {$periodLabel}.",
                    'action_url'      => route('portal.settlements.show', $period->id),
                    'notifiable_type' => MonthlyPeriod::class,
                    'notifiable_id'   => $period->id,
                    'sent_at'         => $now,
                    'created_at'      => $now,
                ])->toArray();

                DB::table('notifications')->insert($rows);

                // Send email to each delegate who has an email address
                foreach ($delegates as $d) {
                    try {
                        $this->emailService->sendToDelegateModel(
                            $d,
                            NotificationCategory::SettlementPublished,
                            "كشف راتبك لشهر {$periodLabel} متاح الآن",
                            "يمكنك الآن مراجعة تفاصيل كشف راتبك الشهري لفترة {$periodLabel}.",
                            route('portal.settlements.show', $period->id),
                        );
                    } catch (\Throwable) {}
                }
            } catch (\Throwable $e) {
                Log::warning('MonthCloseService: Phase 3 notification insert failed', ['error' => $e->getMessage()]);
            }
        }
    }
}
