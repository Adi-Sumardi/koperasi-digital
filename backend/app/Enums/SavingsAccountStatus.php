<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsAccountStatus: string
{
    case ACTIVE = 'active';
    case CLOSED = 'closed';
}
