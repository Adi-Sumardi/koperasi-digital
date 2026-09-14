<?php

declare(strict_types=1);

namespace App\Actions\Members;

use App\Enums\KycStatus;
use App\Enums\MemberStatus;
use App\Enums\SavingsType;
use App\Models\Member;

class ActivateMembershipAction
{
    /**
     * Keanggotaan menjadi AKTIF setelah KYC diverifikasi dan Simpanan Pokok
     * lunas dibayar penuh (BRD.md §5.1). Menerbitkan Nomor Anggota Koperasi (NAK).
     */
    public function activateIfEligible(Member $member): void
    {
        if ($member->status !== MemberStatus::PENDING) {
            return;
        }

        if ($member->kyc?->status !== KycStatus::APPROVED) {
            return;
        }

        $pokok = $member->savingsAccounts()->where('type', SavingsType::POKOK)->first();

        if (! $pokok || (float) $pokok->balance < (float) config('koperasi.simpanan_pokok_amount')) {
            return;
        }

        $member->update([
            'status' => MemberStatus::ACTIVE,
            'member_number' => $this->generateMemberNumber(),
            'joined_at' => now(),
        ]);
    }

    private function generateMemberNumber(): string
    {
        $year = now()->year;
        $sequence = Member::whereYear('joined_at', $year)->count() + 1;

        return sprintf('KOP-%d-%05d', $year, $sequence);
    }
}
