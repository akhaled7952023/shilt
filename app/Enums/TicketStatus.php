<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open             = 'open';
    case InProgress       = 'in_progress';
    case AwaitingDelegate = 'awaiting_delegate';
    case Resolved         = 'resolved';
    case Reopened         = 'reopened';
    case Closed           = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open             => 'مفتوح',
            self::InProgress       => 'قيد المعالجة',
            self::AwaitingDelegate => 'بانتظار ردك',
            self::Resolved         => 'تم الحل',
            self::Reopened         => 'أُعيد فتحه',
            self::Closed           => 'مغلق',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::Open             => 'Open',
            self::InProgress       => 'In Progress',
            self::AwaitingDelegate => 'Awaiting Your Reply',
            self::Resolved         => 'Resolved',
            self::Reopened         => 'Reopened',
            self::Closed           => 'Closed',
        };
    }

    /** Returns true if the delegate is allowed to post a reply in this status. */
    public function allowsDelegateReply(): bool
    {
        return in_array($this, [
            self::Open,
            self::InProgress,
            self::AwaitingDelegate,
            self::Resolved,
            self::Reopened,
        ]);
    }

    /** Returns true when the ticket can be moved to a new status by an admin. */
    public function isActive(): bool
    {
        return $this !== self::Closed;
    }

    /** Bootstrap badge class for the admin queue UI. */
    public function badgeClass(): string
    {
        return match($this) {
            self::Open             => 'badge-info',
            self::InProgress       => 'badge-primary',
            self::AwaitingDelegate => 'badge-warning',
            self::Resolved         => 'badge-success',
            self::Reopened         => 'badge-danger',
            self::Closed           => 'badge-secondary',
        };
    }
}
