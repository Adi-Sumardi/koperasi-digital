<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsTransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case INTEREST = 'interest';
    case TRANSFER = 'transfer';
}
