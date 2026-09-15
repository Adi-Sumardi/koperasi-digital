<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanInstallmentStatus: string
{
    case UNPAID = 'unpaid';
    case PARTIAL = 'partial';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
}
