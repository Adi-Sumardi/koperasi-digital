<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Member
 */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_number' => $this->member_number,
            'full_name' => $this->full_name,
            'employee_nip' => $this->employee_nip,
            'department' => $this->department,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
            'kyc' => new MemberKycResource($this->whenLoaded('kyc')),
            'created_at' => $this->created_at,
        ];
    }
}
