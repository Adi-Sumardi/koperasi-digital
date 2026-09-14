<?php

declare(strict_types=1);

namespace App\Actions\Savings;

use App\Enums\SavingsType;
use App\Models\Member;
use Illuminate\Support\Str;

class CreateMemberSavingsAccountsAction
{
    /**
     * Setiap anggota baru mendapat 3 rekening simpanan (Pokok, Wajib, Sukarela)
     * dengan saldo nol sejak registrasi (BRD.md §5.2).
     */
    public function execute(Member $member): void
    {
        foreach (SavingsType::cases() as $type) {
            $member->savingsAccounts()->create([
                'account_number' => $this->generateAccountNumber($type),
                'type' => $type,
                'balance' => 0,
            ]);
        }
    }

    private function generateAccountNumber(SavingsType $type): string
    {
        $prefix = match ($type) {
            SavingsType::POKOK => 'POK',
            SavingsType::WAJIB => 'WAJ',
            SavingsType::SUKARELA => 'SUK',
        };

        return $prefix.'-'.Str::upper(Str::random(10));
    }
}
