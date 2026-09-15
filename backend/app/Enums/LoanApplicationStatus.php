<?php

declare(strict_types=1);

namespace App\Enums;

enum LoanApplicationStatus: string
{
    case PENDING_REVIEW = 'pending_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
