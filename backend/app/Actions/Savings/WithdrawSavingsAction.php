<?php

declare(strict_types=1);

namespace App\Actions\Savings;

use App\Enums\SavingsTransactionType;
use App\Enums\SavingsType;
use App\Events\Savings\SavingsWithdrawnEvent;
use App\Exceptions\InsufficientBalanceException;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawSavingsAction
{
    public function execute(Member $member, float $amount): SavingsTransaction
    {
        return DB::transaction(function () use ($member, $amount) {
            /** @var SavingsAccount $account */
            $account = $member->savingsAccounts()
                ->where('type', SavingsType::SUKARELA)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $account->balance < $amount) {
                throw new InsufficientBalanceException('Saldo simpanan sukarela tidak mencukupi.');
            }

            $account->decrement('balance', $amount);
            $account->refresh();

            $transaction = $account->transactions()->create([
                'reference_no' => $this->generateReferenceNo(),
                'type' => SavingsTransactionType::WITHDRAWAL,
                'amount' => $amount,
                'balance_after' => $account->balance,
            ]);

            SavingsWithdrawnEvent::dispatch($transaction);

            return $transaction;
        });
    }

    private function generateReferenceNo(): string
    {
        return 'TRX-SAV-'.now()->format('Ymd').'-'.Str::padLeft((string) random_int(0, 999999), 6, '0');
    }
}
