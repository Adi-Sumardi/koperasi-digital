<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\Loans\Data\FlatLoanCalculationResult;
use App\Services\Loans\Data\SlidingLoanCalculationResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FlatLoanCalculationResult|SlidingLoanCalculationResult
 */
class LoanSimulationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $result = $this->resource;

        return [
            'total_repayment' => number_format($result->totalRepayment, 2, '.', ''),
            'monthly_installment' => number_format($result->schedule[0]->totalInstallment, 2, '.', ''),
            'schedule' => array_map(fn ($line) => [
                'month' => $line->month,
                'principal_portion' => number_format($line->principalPortion, 2, '.', ''),
                'interest_portion' => number_format($line->interestPortion, 2, '.', ''),
                'total_installment' => number_format($line->totalInstallment, 2, '.', ''),
                'outstanding_balance_after' => number_format($line->outstandingBalanceAfter, 2, '.', ''),
            ], $result->schedule),
        ];
    }
}
