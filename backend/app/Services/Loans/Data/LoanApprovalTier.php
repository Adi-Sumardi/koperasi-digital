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
    ) {}
}
