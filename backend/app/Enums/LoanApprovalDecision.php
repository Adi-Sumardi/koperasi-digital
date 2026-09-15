<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanApprovalDecision: string
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
