<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanInterestType;
use App\Exceptions\LoanWorkflowException;
use App\Models\LoanProduct;
use App\Services\Loans\Data\FlatLoanCalculationResult;
use App\Services\Loans\Data\SlidingLoanCalculationResult;
use App\Services\Loans\LoanCalculationService;

class SimulateLoanAction
{
    public function __construct(
        private readonly LoanCalculationService $calculator,
    ) {}

    public function execute(LoanProduct $product, float $amount, int $tenorMonths): FlatLoanCalculationResult|SlidingLoanCalculationResult
    {
        if ($tenorMonths < $product->min_tenor_months || $tenorMonths > $product->max_tenor_months) {
            throw new LoanWorkflowException(
                "Tenor untuk produk ini harus antara {$product->min_tenor_months} dan {$product->max_tenor_months} bulan."
            );
        }

        if ($amount > (float) $product->max_ceiling_amount) {
            throw new LoanWorkflowException('Nominal pinjaman melebihi plafon maksimal produk ini.');
        }

        $monthlyRate = $product->monthlyInterestRate();

        return $product->interest_type === LoanInterestType::FLAT
            ? $this->calculator->calculateFlat($amount, $tenorMonths, $monthlyRate)
            : $this->calculator->calculateSliding($amount, $tenorMonths, $monthlyRate);
    }
}
