<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\MemberKyc;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MemberKyc
 */
class MemberKycResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'verified_at' => $this->verified_at,
            'submitted_at' => $this->created_at,
        ];
    }
}
