<?php

namespace App\Enums;

enum TicketSource: string
{
    case Portal    = 'portal';
    case Dashboard = 'dashboard';
    case System    = 'system';

    public function label(): string
    {
        return match($this) {
            self::Portal    => 'بوابة المندوب',
            self::Dashboard => 'لوحة التحكم',
            self::System    => 'النظام',
        };
    }

    /** Icon identifier used in admin queue column. */
    public function icon(): string
    {
        return match($this) {
            self::Portal    => '🌐',
            self::Dashboard => '🖥',
            self::System    => '⚙',
        };
    }
}
