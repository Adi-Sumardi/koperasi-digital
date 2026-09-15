<?php

declare(strict_types=1);

namespace App\Services\Loans\Data;

final class SlidingLoanCalculationResult
{
    /**
     * @param  array<int, LoanInstallmentLine>  $schedule
     */
    public function __construct(
        public readonly float $totalPrincipal,
        public readonly float $totalInterest,
        public readonly float $totalRepayment,
        public readonly array $schedule,
    ) {}
}
