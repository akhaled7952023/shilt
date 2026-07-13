<?php

namespace App\Enums;

enum PendingEntryStatus: string
{
    case Pending   = 'pending';
    case Imported  = 'imported';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'بانتظار الاستيراد',
            self::Imported  => 'مُستورد',
            self::Cancelled => 'مُلغى',
        };
    }
}
