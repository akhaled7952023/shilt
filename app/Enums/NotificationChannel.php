<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Portal   = 'portal';
    case Email    = 'email';
    case Sms      = 'sms';
    case Whatsapp = 'whatsapp';
}
