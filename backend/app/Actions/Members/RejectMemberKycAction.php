<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Enums\KycStatus;
use App\Models\MemberKyc;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class RejectMemberKycAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    public function execute(MemberKyc $kyc, User $verifier, string $reason): MemberKyc
    {
        $previousStatus = $kyc->status->value;

        $kyc->update([
            'status' => KycStatus::REJECTED,
            'rejection_reason' => $reason,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ]);

        $this->auditLogger->log(
            event: 'member.kyc_rejected',
            actor: $verifier,
            subject: $kyc,
            old: ['status' => $previousStatus],
            new: ['status' => KycStatus::REJECTED->value, 'reason' => $reason],
        );

        return $kyc->fresh();
    }
}
