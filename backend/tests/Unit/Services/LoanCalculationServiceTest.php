<?php

declare(strict_types=1);

use App\Services\Loans\LoanCalculationService;

describe('LoanCalculationService', function () {
    it('calculates flat interest installments correctly', function () {
        $service = new LoanCalculationService;

        // Pinjaman: Rp 12.000.000, Tenor: 12 Bulan, Bunga: 0.8% flat per bulan
        $result = $service->calculateFlat(
            principal: 12000000.00,
            tenorMonths: 12,
            monthlyRate: 0.008
        );

        // Pokok per bulan: 1.000.000
        // Bunga per bulan: 96.000
        // Total angsuran per bulan: 1.096.000
        expect($result->monthlyPrincipal)->toEqual(1000000.00)
            ->and($result->monthlyInterest)->toEqual(96000.00)
            ->and($result->totalMonthlyInstallment)->toEqual(1096000.00)
            ->and($result->totalRepayment)->toEqual(13152000.00);

        expect($result->schedule)->toHaveCount(12);
        expect(array_sum(array_column($result->schedule, 'principalPortion')))->toEqual(12000000.00);
    });

    it('calculates a declining interest portion for the sliding method', function () {
        $service = new LoanCalculationService;

        $result = $service->calculateSliding(
            principal: 12000000.00,
            tenorMonths: 12,
            monthlyRate: 0.008
        );

        expect($result->schedule[0]->interestPortion)->toEqual(96000.00) // 12.000.000 x 0.8%
            ->and($result->schedule[0]->principalPortion)->toEqual(1000000.00);

        // Bunga bulan berikutnya lebih kecil karena sisa pokok sudah menurun.
        expect($result->schedule[1]->interestPortion)->toBeLessThan($result->schedule[0]->interestPortion);

        expect(array_sum(array_column($result->schedule, 'principalPortion')))->toEqual(12000000.00);
        expect($result->schedule[11]->outstandingBalanceAfter)->toEqual(0.00);

        // Total bunga sliding selalu lebih kecil dari flat untuk plafon & tenor yang sama.
        $flat = $service->calculateFlat(12000000.00, 12, 0.008);
        expect($result->totalInterest)->toBeLessThan($flat->totalMonthlyInstallment * 12 - 12000000.00);
    });
});
