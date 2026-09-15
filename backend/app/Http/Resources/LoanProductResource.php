<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LoanProduct
 */
class LoanProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'interest_type' => $this->interest_type,
            'annual_interest_rate' => (string) $this->annual_interest_rate,
            'min_tenor_months' => $this->min_tenor_months,
            'max_tenor_months' => $this->max_tenor_months,
            'max_ceiling_amount' => (string) $this->max_ceiling_amount,
        ];
    }
}
