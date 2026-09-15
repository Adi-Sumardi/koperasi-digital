<?php

declare(strict_types=1);

namespace App\Services\Loans\Data;

final class FlatLoanCalculationResult
{
    /**
     * @param  array<int, LoanInstallmentLine>  $schedule
     */
    public function __construct(
        public readonly float $monthlyPrincipal,
        public readonly float $monthlyInterest,
        public readonly float $totalMonthlyInstallment,
        public readonly float $totalRepayment,
        public readonly array $schedule,
    ) {}
}
