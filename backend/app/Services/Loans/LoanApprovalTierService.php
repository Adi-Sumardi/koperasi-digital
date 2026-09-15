<?php

declare(strict_types=1);

namespace App\Services\Loans;

use App\Services\Loans\Data\LoanApprovalTier;

/**
 * Jenjang persetujuan berjenjang berdasarkan nominal pengajuan (BRD.md §5.3.3).
 */
class LoanApprovalTierService
{
    public function resolve(float $amount): LoanApprovalTier
    {
        $tiers = config('koperasi.loan.approval_tiers');

        foreach ($tiers as $tier) {
            if ($tier['max_amount'] === null || $amount <= $tier['max_amount']) {
                return new LoanApprovalTier(
                    requiredApprovals: $tier['required_approvals'],
                    roles: $tier['roles'],
                    requiresTwoFactor: $tier['max_amount'] === null,
                );
            }
        }

        // Tidak akan pernah tercapai selama tier terakhir memiliki max_amount = null.
        $last = end($tiers);

        return new LoanApprovalTier($last['required_approvals'], $last['roles'], requiresTwoFactor: true);
    }
}
