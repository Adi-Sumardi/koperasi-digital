<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Loan
 */
class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'loan_number' => $this->loan_number,
            'principal_amount' => (string) $this->principal_amount,
            'interest_rate' => (string) $this->interest_rate,
            'tenor_months' => $this->tenor_months,
            'monthly_installment' => (string) $this->monthly_installment,
            'outstanding_balance' => (string) $this->outstanding_balance,
            'status' => $this->status,
            'disbursed_at' => $this->disbursed_at,
            'installments' => LoanInstallmentResource::collection($this->whenLoaded('installments')),
        ];
    }
}
