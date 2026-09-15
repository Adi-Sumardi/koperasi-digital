<?php

declare(strict_types=1);

namespace App\Services\Loans\Data;

final class LoanApprovalTier
{
    /**
     * @param  array<int, string>  $roles
     */
    public function __construct(
        public readonly int $requiredApprovals,
        public readonly array $roles,
        // Jenjang nominal tertinggi (tanpa batas atas) mewajibkan verifikasi 2FA
        // TOTP tambahan saat menyetujui (rules/security.md §1).
        public readonly bool $requiresTwoFactor = false,
    ) {}
}
