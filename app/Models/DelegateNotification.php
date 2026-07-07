<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MonthlyPeriod;

class DelegateNotification extends Model
{
    protected $fillable = ['delegate_id', 'type', 'title', 'body', 'data', 'read_at'];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Delegate::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function markAsRead(): void
    {
        if ($this->isUnread()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function typeIcon(): string
    {
        return match($this->type) {
            'settlement_published' => 'la-file-text',
            'password_reset'       => 'la-lock',
            'portal_enabled'       => 'la-check-circle',
            'portal_disabled'      => 'la-ban',
            'announcement'         => 'la-bullhorn',
            default                => 'la-bell',
        };
    }

    public function typeColor(): string
    {
        return match($this->type) {
            'settlement_published' => '#16a34a',
            'password_reset'       => '#d97706',
            'portal_enabled'       => '#2563eb',
            'portal_disabled'      => '#dc2626',
            'announcement'         => '#7c3aed',
            default                => '#64748b',
        };
    }

    public function typeBg(): string
    {
        return match($this->type) {
            'settlement_published' => '#f0fdf4',
            'password_reset'       => '#fffbeb',
            'portal_enabled'       => '#eff6ff',
            'portal_disabled'      => '#fff1f2',
            'announcement'         => '#fdf4ff',
            default                => '#f1f5f9',
        };
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function getLocalizedTitle(): string
    {
        return match($this->type) {
            'settlement_published' => __('portal.notif_settlement_published_title', [
                'period' => $this->getPeriodLabel(),
            ]),
            'password_reset'   => __('portal.notif_password_reset_title'),
            'portal_enabled'   => __('portal.notif_portal_enabled_title'),
            'portal_disabled'  => __('portal.notif_portal_disabled_title'),
            default            => $this->title,
        };
    }

    public function getLocalizedBody(): ?string
    {
        return match($this->type) {
            'settlement_published' => __('portal.notif_settlement_published_body', [
                'period' => $this->getPeriodLabel(),
            ]),
            'password_reset'   => __('portal.notif_password_reset_body'),
            'portal_enabled'   => __('portal.notif_portal_enabled_body'),
            'portal_disabled'  => __('portal.notif_portal_disabled_body'),
            default            => $this->body,
        };
    }

    private function getPeriodLabel(): string
    {
        $data = $this->data ?? [];

        // Best path: load the period model and use its locale-aware display label.
        if (!empty($data['period_id'])) {
            $period = MonthlyPeriod::find($data['period_id']);
            if ($period) {
                return $period->getDisplayLabel();
            }
        }

        // Fallback for English when month/year were stored explicitly.
        if (app()->getLocale() === 'en') {
            $month = $data['period_month'] ?? null;
            $year  = $data['period_year']  ?? null;
            if ($month && $year) {
                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
                ];
                return ($months[(int)$month] ?? (string)$month) . ' ' . $year;
            }
        }

        return $data['period_label'] ?? $this->title;
    }
}
