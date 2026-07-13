<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 3 — Unified notification record.
 * Table: `notifications` (NEW Phase 3 table).
 *
 * Distinct from App\Models\DelegateNotification which uses the `delegate_notifications` table.
 * This model covers all channels (portal, email, sms, whatsapp) for both delegates and admins.
 *
 * @property int                      $id
 * @property string                   $recipient_type  'delegate' | 'admin'
 * @property int                      $recipient_id    delegate_id or user_id
 * @property NotificationChannel      $channel
 * @property NotificationCategory     $category
 * @property string                   $title
 * @property string                   $body
 * @property string|null              $action_url
 * @property \Carbon\Carbon|null      $read_at
 * @property \Carbon\Carbon|null      $sent_at
 * @property \Carbon\Carbon|null      $failed_at
 * @property string|null              $notifiable_type
 * @property int|null                 $notifiable_id
 * @property array|null               $data
 * @property \Carbon\Carbon           $created_at
 */
class SupportNotification extends Model
{
    protected $table = 'notifications';

    public $timestamps = false;

    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'channel',
        'category',
        'title',
        'body',
        'action_url',
        'read_at',
        'sent_at',
        'failed_at',
        'notifiable_type',
        'notifiable_id',
        'data',
    ];

    protected $casts = [
        'channel'    => NotificationChannel::class,
        'category'   => NotificationCategory::class,
        'data'       => 'array',
        'read_at'    => 'datetime',
        'sent_at'    => 'datetime',
        'failed_at'  => 'datetime',
        'created_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForRecipient($query, string $type, int $id)
    {
        return $query->where('recipient_type', $type)->where('recipient_id', $id);
    }

    public function scopePortalChannel($query)
    {
        return $query->where('channel', NotificationChannel::Portal->value);
    }

    // ── Display helpers (mirrors DelegateNotification interface) ─────────────

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function typeIcon(): string
    {
        return match($this->category) {
            NotificationCategory::TicketNew                     => 'la-ticket-alt',
            NotificationCategory::TicketReply                   => 'la-comment-dots',
            NotificationCategory::TicketReopened                => 'la-redo',
            NotificationCategory::TicketClosed                  => 'la-check-circle',
            NotificationCategory::SettlementPublished           => 'la-file-invoice',
            NotificationCategory::SettlementViewed              => 'la-eye',
            NotificationCategory::FinancialRequestApproved      => 'la-money-bill',
            NotificationCategory::FinancialRequestRejected      => 'la-times-circle',
            NotificationCategory::IqamaExpiring,
            NotificationCategory::PassportExpiring,
            NotificationCategory::DrivingLicenseExpiring        => 'la-id-card',
            NotificationCategory::VehicleRegistrationExpiring,
            NotificationCategory::VehicleInsuranceExpiring      => 'la-car',
            default                                             => 'la-bell',
        };
    }

    public function typeColor(): string
    {
        return match($this->category) {
            NotificationCategory::TicketNew                     => '#2563eb',
            NotificationCategory::TicketReply                   => '#2563eb',
            NotificationCategory::TicketReopened                => '#d97706',
            NotificationCategory::TicketClosed                  => '#64748b',
            NotificationCategory::SettlementPublished           => '#16a34a',
            NotificationCategory::SettlementViewed              => '#0891b2',
            NotificationCategory::FinancialRequestApproved      => '#16a34a',
            NotificationCategory::FinancialRequestRejected      => '#dc2626',
            NotificationCategory::IqamaExpiring,
            NotificationCategory::PassportExpiring,
            NotificationCategory::DrivingLicenseExpiring,
            NotificationCategory::VehicleRegistrationExpiring,
            NotificationCategory::VehicleInsuranceExpiring      => '#b45309',
            default                                             => '#64748b',
        };
    }

    public function typeBg(): string
    {
        return match($this->category) {
            NotificationCategory::TicketNew                     => '#eff6ff',
            NotificationCategory::TicketReply                   => '#eff6ff',
            NotificationCategory::TicketReopened                => '#fffbeb',
            NotificationCategory::TicketClosed                  => '#f1f5f9',
            NotificationCategory::SettlementPublished           => '#f0fdf4',
            NotificationCategory::SettlementViewed              => '#ecfeff',
            NotificationCategory::FinancialRequestApproved      => '#f0fdf4',
            NotificationCategory::FinancialRequestRejected      => '#fff1f2',
            NotificationCategory::IqamaExpiring,
            NotificationCategory::PassportExpiring,
            NotificationCategory::DrivingLicenseExpiring,
            NotificationCategory::VehicleRegistrationExpiring,
            NotificationCategory::VehicleInsuranceExpiring      => '#fffbeb',
            default                                             => '#f1f5f9',
        };
    }

    public function getLocalizedTitle(): string
    {
        return $this->title ?? '';
    }

    public function getLocalizedBody(): ?string
    {
        return $this->body ?: null;
    }
}
