<?php

declare(strict_types=1);

namespace App\Services\Loans;

use App\Services\Loans\Data\FlatLoanCalculationResult;
use App\Services\Loans\Data\LoanInstallmentLine;
use App\Services\Loans\Data\SlidingLoanCalculationResult;

/**
 * Kalkulator simulasi bunga pinjaman (BRD.md §5.3.2).
 */
class LoanCalculationService
{
    /**
     * Bunga Tetap (Flat Rate): bunga dihitung dari plafon awal, konstan setiap bulan.
     */
    public function calculateFlat(float $principal, int $tenorMonths, float $monthlyRate): FlatLoanCalculationResult
    {
        $monthlyPrincipal = round($principal / $tenorMonths, 2);
        $monthlyInterest = round($principal * $monthlyRate, 2);
        $totalMonthlyInstallment = round($monthlyPrincipal + $monthlyInterest, 2);

        $schedule = [];
        $outstanding = $principal;
        $allocatedPrincipal = 0.0;

        for ($month = 1; $month <= $tenorMonths; $month++) {
            // Cicilan terakhir menyerap sisa pembulatan agar total pokok tepat sama dengan plafon.
            $principalPortion = $month === $tenorMonths
                ? round($principal - $allocatedPrincipal, 2)
                : $monthlyPrincipal;

            $allocatedPrincipal += $principalPortion;
            $outstanding = round($outstanding - $principalPortion, 2);

            $schedule[] = new LoanInstallmentLine(
                month: $month,
                principalPortion: $principalPortion,
                interestPortion: $monthlyInterest,
                totalInstallment: round($principalPortion + $monthlyInterest, 2),
                outstandingBalanceAfter: max($outstanding, 0.0),
            );
        }

        return new FlatLoanCalculationResult(
            monthlyPrincipal: $monthlyPrincipal,
            monthlyInterest: $monthlyInterest,
            totalMonthlyInstallment: $totalMonthlyInstallment,
            totalRepayment: round($totalMonthlyInstallment * $tenorMonths, 2),
            schedule: $schedule,
        );
    }

    /**
     * Bunga Menurun (Sliding/Effective Rate): pokok per bulan konstan seperti flat,
     * namun bunga dihitung dari sisa pokok bulan sebelumnya sehingga menurun setiap
     * bulan (BRD.md §5.3.2: "Bunga Bulan Ke-n = Sisa Pokok Bulan(n-1) × Rate Bulanan").
     */
    public function calculateSliding(float $principal, int $tenorMonths, float $monthlyRate): SlidingLoanCalculationResult
    {
        $monthlyPrincipal = round($principal / $tenorMonths, 2);

        $schedule = [];
        $outstanding = $principal;
        $allocatedPrincipal = 0.0;
        $totalInterest = 0.0;

        for ($month = 1; $month <= $tenorMonths; $month++) {
            $principalPortion = $month === $tenorMonths
                ? round($principal - $allocatedPrincipal, 2)
                : $monthlyPrincipal;

            $interestPortion = round($outstanding * $monthlyRate, 2);

            $allocatedPrincipal += $principalPortion;
            $totalInterest += $interestPortion;
            $outstanding = round($outstanding - $principalPortion, 2);

            $schedule[] = new LoanInstallmentLine(
                month: $month,
                principalPortion: $principalPortion,
                interestPortion: $interestPortion,
                totalInstallment: round($principalPortion + $interestPortion, 2),
                outstandingBalanceAfter: max($outstanding, 0.0),
            );
        }

        return new SlidingLoanCalculationResult(
            totalPrincipal: $principal,
            totalInterest: round($totalInterest, 2),
            totalRepayment: round($principal + $totalInterest, 2),
            schedule: $schedule,
        );
    }
}
