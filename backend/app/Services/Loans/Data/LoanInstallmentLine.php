<?php

declare(strict_types=1);

namespace App\Services\Loans\Data;

final class LoanInstallmentLine
{
    public function __construct(
        public readonly int $month,
        public readonly float $principalPortion,
        public readonly float $interestPortion,
        public readonly float $totalInstallment,
        public readonly float $outstandingBalanceAfter,
    ) {}
}
