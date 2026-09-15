<?php

declare(strict_types=1);

namespace App\Services\Loans;

use App\Models\Member;

/**
 * Aturan plafon & Debt Service Ratio (BRD.md §5.3.1).
 */
class LoanEligibilityService
{
    public function maxCeiling(Member $member): float
    {
        $multiplier = (float) config('koperasi.loan.ceiling_multiplier');

        return round($member->totalSavingsBalance() * $multiplier, 2);
    }

    public function passesDebtServiceRatio(Member $member, float $monthlyInstallment): bool
    {
        $maxRatio = (float) config('koperasi.loan.dsr_max_ratio');
        $monthlySalary = (float) $member->monthly_salary;

        if ($monthlySalary <= 0) {
            return false;
        }

        return $monthlyInstallment <= round($monthlySalary * $maxRatio, 2);
    }
}
