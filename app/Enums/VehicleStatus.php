<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Available   = 'available';
    case Assigned    = 'assigned';
    case Maintenance = 'maintenance';
    case Retired     = 'retired';

    public function label(): string
    {
        return match($this) {
            self::Available   => 'متاح',
            self::Assigned    => 'مُعيَّن',
            self::Maintenance => 'صيانة',
            self::Retired     => 'متقاعد',
        };
    }
}
