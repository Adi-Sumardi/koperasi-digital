<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Enums\KycStatus;
use App\Models\MemberKyc;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ApproveMemberKycAction
{
    public function __construct(
        private readonly ActivateMembershipAction $activateMembership,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(MemberKyc $kyc, User $verifier): MemberKyc
    {
        return DB::transaction(function () use ($kyc, $verifier) {
            $previousStatus = $kyc->status->value;

            $kyc->update([
                'status' => KycStatus::APPROVED,
                'rejection_reason' => null,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
            ]);

            $this->auditLogger->log(
                event: 'member.kyc_approved',
                actor: $verifier,
                subject: $kyc,
                old: ['status' => $previousStatus],
                new: ['status' => KycStatus::APPROVED->value],
            );

            $this->activateMembership->activateIfEligible($kyc->member->refresh());

            return $kyc->fresh();
        });
    }
}
