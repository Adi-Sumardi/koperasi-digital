<?php

declare(strict_types=1);

namespace App\Events\Loans;

use App\Models\LoanInstallment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LoanInstallmentRepaidEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly LoanInstallment $installment
    ) {}
}
