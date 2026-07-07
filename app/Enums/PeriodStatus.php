<?php

namespace App\Enums;

enum PeriodStatus: string
{
    case Open      = 'open';
    case Editing   = 'editing';
    case Approved  = 'approved';
    case Published = 'published';
    case Closed    = 'closed';

    public function isVisibleToDelegate(): bool
    {
        return in_array($this, [self::Published, self::Closed]);
    }

    public function canTransitionTo(PeriodStatus $next): bool
    {
        return match($this) {
            self::Open      => $next === self::Editing,
            self::Editing   => in_array($next, [self::Approved, self::Open]),
            self::Approved  => in_array($next, [self::Published, self::Editing]),
            self::Published => $next === self::Closed,
            self::Closed    => $next === self::Open,
        };
    }
}
