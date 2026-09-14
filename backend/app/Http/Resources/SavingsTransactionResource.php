<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SavingsTransaction
 */
class SavingsTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'type' => $this->type,
            'amount' => (string) $this->amount,
            'balance_after' => (string) $this->balance_after,
            'created_at' => $this->created_at,
        ];
    }
}
