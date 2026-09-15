<?php

declare(strict_types=1);

namespace App\Events\Loans;

use App\Models\Loan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class LoanDisbursedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Loan $loan
    ) {}
}
