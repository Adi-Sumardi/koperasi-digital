<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SavingsAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SavingsAccount
 */
class SavingsAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_number' => $this->account_number,
            'type' => $this->type,
            'balance' => (string) $this->balance,
            'status' => $this->status,
        ];
    }
}
