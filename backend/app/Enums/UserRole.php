<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case MEMBER = 'member';
    case TREASURER = 'treasurer';
    case CHAIRMAN = 'chairman';
    case AUDITOR = 'auditor';
    case SUPERADMIN = 'superadmin';
}
