<?php

namespace App\Enums;

enum FinancialRequestStatus: string
{
    case Pending   = 'pending';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case NeedsInfo = 'needs_info';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'بانتظار المراجعة',
            self::Approved  => 'مُوافق عليه',
            self::Rejected  => 'مرفوض',
            self::NeedsInfo => 'بانتظار معلومات إضافية',
        };
    }

    public function isReviewed(): bool
    {
        return match($this) {
            self::Approved, self::Rejected => true,
            default => false,
        };
    }
}
