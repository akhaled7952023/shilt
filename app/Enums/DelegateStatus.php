<?php

namespace App\Enums;

enum DelegateStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match($this) {
            self::Active     => __('portal.delegate_status_active'),
            self::Inactive   => __('portal.delegate_status_inactive'),
            self::Suspended  => __('portal.delegate_status_suspended'),
            self::Terminated => __('portal.delegate_status_terminated'),
        };
    }
}
