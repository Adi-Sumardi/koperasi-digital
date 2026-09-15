<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Enums\KycStatus;
use App\Models\MemberKyc;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveMemberKycAction
{
    public function __construct(
        private readonly ActivateMembershipAction $activateMembership,
    ) {}

    public function execute(MemberKyc $kyc, User $verifier): MemberKyc
    {
        return DB::transaction(function () use ($kyc, $verifier) {
            $kyc->update([
                'status' => KycStatus::APPROVED,
                'rejection_reason' => null,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            $this->activateMembership->activateIfEligible($kyc->member->refresh());

            return $kyc->fresh();
        });
    }
}
