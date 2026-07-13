<?php

namespace App\Mail;

use App\Enums\NotificationCategory;
use App\Models\SystemSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class DelegateNotificationMail extends Mailable
{
    public function __construct(
        public readonly NotificationCategory $category,
        public readonly string $notifTitle,
        public readonly string $notifBody,
        public readonly ?string $actionUrl = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->notifTitle);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: $this->sharedViewData(),
        );
    }

    private function sharedViewData(): array
    {
        $logoPath   = SystemSetting::get('company_logo_path');
        $companyAr  = SystemSetting::get('company_name_ar') ?? config('app.name');

        return [
            'title'       => $this->notifTitle,
            'body'        => $this->notifBody,
            'actionUrl'   => $this->actionUrl,
            'category'    => $this->category,
            'logoUrl'     => $logoPath ? url('/storage/' . $logoPath) : null,
            'companyName' => $companyAr,
        ];
    }
}
