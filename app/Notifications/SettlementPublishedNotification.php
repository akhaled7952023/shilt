<?php

namespace App\Notifications;

use App\Models\DelegateMonthlyEntry;
use App\Models\MonthlyPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SettlementPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MonthlyPeriod $period, public DelegateMonthlyEntry $entry)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("تسوية {$this->period->label()} متاحة")
            ->line("تم نشر تسوية الفترة {$this->period->label()}")
            ->line("صافي التسوية: {$this->entry->net_settlement} ريال");
    }
}
