<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanStatus: string
{
    case ACTIVE = 'active';
    case PAID_OFF = 'paid_off';
    case DEFAULTED = 'defaulted';
}
