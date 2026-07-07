<?php

namespace App\Services;

use App\Models\Delegate;
use App\Models\DelegateNotification;
use App\Models\MonthlyPeriod;

class DelegateNotificationService
{
    public function notify(
        int $delegateId,
        string $type,
        string $title,
        ?string $body = null,
        array $data = []
    ): DelegateNotification {
        return DelegateNotification::create([
            'delegate_id' => $delegateId,
            'type'        => $type,
            'title'       => $title,
            'body'        => $body,
            'data'        => $data ?: null,
        ]);
    }

    public function notifySettlementPublished(Delegate $delegate, MonthlyPeriod $period): void
    {
        $label = $period->getDisplayLabel();
        $this->notify(
            $delegate->id,
            'settlement_published',
            "تسوية جديدة متاحة — {$label}",
            "تم اعتماد ونشر تسويتك الشهرية لفترة {$label}. يمكنك الاطلاع على التفاصيل الكاملة الآن.",
            [
                'period_id'    => $period->id,
                'period_label' => $label,
                'period_month' => $period->month,
                'period_year'  => $period->year,
            ]
        );
    }

    public function notifyPasswordReset(Delegate $delegate): void
    {
        $this->notify(
            $delegate->id,
            'password_reset',
            'تم إعادة تعيين كلمة المرور',
            'قام المشرف بإعادة تعيين كلمة مرور حسابك في بوابة المناديب. سجّل الدخول وغيّر كلمة المرور فور وصولك.'
        );
    }

    public function notifyPortalEnabled(Delegate $delegate): void
    {
        $this->notify(
            $delegate->id,
            'portal_enabled',
            'تم تفعيل حسابك في البوابة',
            'مرحباً بك! تم تفعيل وصولك إلى بوابة المناديب الإلكترونية. يمكنك الآن الاطلاع على تسوياتك ومعلوماتك الشخصية.'
        );
    }

    public function notifyPortalDisabled(Delegate $delegate): void
    {
        $this->notify(
            $delegate->id,
            'portal_disabled',
            'تم إيقاف وصولك إلى البوابة',
            'تم إيقاف وصولك إلى بوابة المناديب الإلكترونية مؤقتاً. تواصل مع مشرفك لمزيد من المعلومات.'
        );
    }

    public function notifyAnnouncement(int|array $delegateIds, string $title, ?string $body = null): void
    {
        $ids = is_array($delegateIds) ? $delegateIds : [$delegateIds];
        $now = now();

        foreach (array_chunk($ids, 500) as $chunk) {
            $rows = array_map(fn($id) => [
                'delegate_id' => $id,
                'type'        => 'announcement',
                'title'       => $title,
                'body'        => $body,
                'data'        => null,
                'read_at'     => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ], $chunk);
            DelegateNotification::insert($rows);
        }
    }
}
