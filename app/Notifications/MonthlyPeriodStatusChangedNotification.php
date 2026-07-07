<?php

namespace App\Notifications;

use App\Enums\PeriodStatus;
use App\Models\MonthlyPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyPeriodStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MonthlyPeriod $period, public PeriodStatus $newStatus)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("تغيير حالة الفترة: {$this->period->label()}")
            ->line("تم تغيير حالة الفترة {$this->period->label()} إلى: {$this->newStatus->value}");
    }
}
