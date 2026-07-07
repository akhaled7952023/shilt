<?php

namespace App\Notifications;

use App\Models\DocumentType;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DocumentType $documentType,
        public string $entityName,
        public Carbon $expiryDate
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تنبيه: وثيقة على وشك الانتهاء')
            ->line("الوثيقة: {$this->documentType->getTranslation('name', 'ar')}")
            ->line("الجهة: {$this->entityName}")
            ->line("تنتهي في: {$this->expiryDate->format('Y-m-d')}");
    }
}
