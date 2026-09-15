<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LoanInstallment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LoanInstallment
 */
class LoanInstallmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'installment_number' => $this->installment_number,
            'due_date' => $this->due_date,
            'principal_portion' => (string) $this->principal_portion,
            'interest_portion' => (string) $this->interest_portion,
            'total_installment' => (string) $this->total_installment,
            'paid_amount' => (string) $this->paid_amount,
            'status' => $this->status,
            'paid_at' => $this->paid_at,
        ];
    }
}
