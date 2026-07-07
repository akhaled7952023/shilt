<?php

namespace App\Enums;

enum PaymentBy: string
{
    case Company  = 'company';
    case Delegate = 'delegate';
}
