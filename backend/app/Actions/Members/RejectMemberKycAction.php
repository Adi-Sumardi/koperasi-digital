<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Enums\KycStatus;
use App\Models\MemberKyc;
use App\Models\User;

class RejectMemberKycAction
{
    public function execute(MemberKyc $kyc, User $verifier, string $reason): MemberKyc
    {
        $kyc->update([
            'status' => KycStatus::REJECTED,
            'rejection_reason' => $reason,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        return $kyc->fresh();
    }
}
