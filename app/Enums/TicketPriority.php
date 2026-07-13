<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low    = 'low';
    case Normal = 'normal';
    case High   = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match($this) {
            self::Low    => 'منخفض',
            self::Normal => 'عادي',
            self::High   => 'عالي',
            self::Urgent => 'عاجل',
        };
    }

    public function labelEn(): string
    {
        return match($this) {
            self::Low    => 'Low',
            self::Normal => 'Normal',
            self::High   => 'High',
            self::Urgent => 'Urgent',
        };
    }

    /** Colour token used in the admin queue UI (Tailwind class names). */
    public function colourClass(): string
    {
        return match($this) {
            self::Low    => 'text-slate-500',
            self::Normal => 'text-blue-600',
            self::High   => 'text-amber-600',
            self::Urgent => 'text-red-600',
        };
    }
}
