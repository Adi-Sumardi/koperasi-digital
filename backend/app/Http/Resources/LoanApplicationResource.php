<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\LoanApplication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LoanApplication
 */
class LoanApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'amount' => (string) $this->amount,
            'tenor_months' => $this->tenor_months,
            'purpose' => $this->purpose,
            'guarantee_type' => $this->guarantee_type,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'decided_at' => $this->decided_at,
            'created_at' => $this->created_at,
        ];
    }
}
