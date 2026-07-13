<?php

namespace App\Console\Commands;

use App\Enums\NotificationCategory;
use App\Mail\AdminNotificationMail;
use App\Mail\DelegateNotificationMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Batch 7 — Safe test-mail command for verifying SMTP configuration.
 * Sends a sample email and reports success/failure.
 * Safe to run in any environment — it only sends to the --to address you specify.
 */
class SendTestEmail extends Command
{
    protected $signature = 'mail:test
                            {--to= : Recipient email address (required)}
                            {--type=delegate : Mail type: delegate|admin}';

    protected $description = 'Send a test notification email to verify SMTP configuration';

    public function handle(): int
    {
        $to   = $this->option('to');
        $type = $this->option('type');

        if (! $to || ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error('Please provide a valid email with --to=you@example.com');
            return Command::FAILURE;
        }

        $category  = NotificationCategory::SettlementPublished;
        $title     = 'اختبار إشعار البريد الإلكتروني';
        $body      = 'هذه رسالة اختبارية من نظام منادب للتأكد من صحة إعدادات البريد الإلكتروني. '
                   . 'تم الإرسال بتاريخ ' . now()->format('Y-m-d H:i:s') . '.';
        $actionUrl = config('app.url');

        $this->line("Sending {$type} test email to: {$to}");
        $this->line('SMTP host: ' . config('mail.mailers.smtp.host'));
        $this->line('SMTP port: ' . config('mail.mailers.smtp.port'));
        $this->line('From:      ' . config('mail.from.address'));

        try {
            $mailable = $type === 'admin'
                ? new AdminNotificationMail($category, $title, $body, $actionUrl)
                : new DelegateNotificationMail($category, $title, $body, $actionUrl);

            Mail::to($to)->send($mailable);

            $this->info("✓ Email sent successfully to {$to}");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('✗ Email failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
