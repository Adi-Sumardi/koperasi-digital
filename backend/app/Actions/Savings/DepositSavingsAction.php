<?php

declare(strict_types=1);

namespace App\Actions\Savings;

use App\Actions\Members\ActivateMembershipAction;
use App\Enums\SavingsTransactionType;
use App\Enums\SavingsType;
use App\Events\Savings\SavingsDepositedEvent;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepositSavingsAction
{
    public function __construct(
        private readonly ActivateMembershipAction $activateMembership,
    ) {}

    public function execute(Member $member, SavingsType $type, float $amount): SavingsTransaction
    {
        return DB::transaction(function () use ($member, $type, $amount) {
            /** @var SavingsAccount $account */
            $account = $member->savingsAccounts()
                ->where('type', $type)
                ->lockForUpdate()
                ->firstOrFail();

            $account->increment('balance', $amount);
            $account->refresh();

            $transaction = $account->transactions()->create([
                'reference_no' => $this->generateReferenceNo(),
                'type' => SavingsTransactionType::DEPOSIT,
                'amount' => $amount,
                'balance_after' => $account->balance,
            ]);

            if ($type === SavingsType::POKOK) {
                $this->activateMembership->activateIfEligible($member->refresh());
            }

            SavingsDepositedEvent::dispatch($transaction);

            return $transaction;
        });
    }

    private function generateReferenceNo(): string
    {
        return 'TRX-SAV-'.now()->format('Ymd').'-'.Str::padLeft((string) random_int(0, 999999), 6, '0');
    }
}
