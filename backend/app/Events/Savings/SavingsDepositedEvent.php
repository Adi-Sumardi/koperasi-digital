<?php

declare(strict_types=1);

namespace App\Events\Savings;

use App\Models\SavingsTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SavingsDepositedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SavingsTransaction $transaction
    ) {}
}
