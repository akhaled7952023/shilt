<?php

namespace App\Enums;

enum ActivityActorType: string
{
    case Delegate = 'delegate';
    case Admin    = 'admin';
    case System   = 'system';
}
