<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\SavingsTransaction;
use Illuminate\Support\Str;

class JournalPostingService
{
    private const KAS_ACCOUNT_CODE = '1-1000';

    private const SAVINGS_LIABILITY_CODES = [
        'pokok' => '2-1001',
        'wajib' => '2-1002',
        'sukarela' => '2-1003',
    ];

    /**
     * Setoran simpanan: Debit Kas, Kredit Liabilitas Simpanan (per jenis).
     */
    public function recordSavingsDeposit(SavingsTransaction $transaction): JournalEntry
    {
        $account = $transaction->savingsAccount;

        return $this->postBalancedEntry(
            description: "Setoran {$account->type->label()} — {$transaction->reference_no}",
            referenceType: 'savings_transaction',
            referenceId: $transaction->id,
            debitAccountCode: self::KAS_ACCOUNT_CODE,
            creditAccountCode: self::SAVINGS_LIABILITY_CODES[$account->type->value],
            amount: (float) $transaction->amount,
        );
    }

    /**
     * Penarikan simpanan sukarela: Debit Liabilitas Simpanan, Kredit Kas.
     */
    public function recordSavingsWithdrawal(SavingsTransaction $transaction): JournalEntry
    {
        $account = $transaction->savingsAccount;

        return $this->postBalancedEntry(
            description: "Penarikan {$account->type->label()} — {$transaction->reference_no}",
            referenceType: 'savings_transaction',
            referenceId: $transaction->id,
            debitAccountCode: self::SAVINGS_LIABILITY_CODES[$account->type->value],
            creditAccountCode: self::KAS_ACCOUNT_CODE,
            amount: (float) $transaction->amount,
        );
    }

    private function postBalancedEntry(
        string $description,
        string $referenceType,
        string $referenceId,
        string $debitAccountCode,
        string $creditAccountCode,
        float $amount,
    ): JournalEntry {
        $debitAccount = ChartOfAccount::where('code', $debitAccountCode)->firstOrFail();
        $creditAccount = ChartOfAccount::where('code', $creditAccountCode)->firstOrFail();

        $entry = JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'is_posted' => true,
        ]);

        // Kedua baris disisipkan dalam transaksi DB yang sama (lihat pemanggil di listener);
        // trigger keseimbangan debit=kredit divalidasi DEFERRED, saat COMMIT.
        $entry->lines()->create([
            'account_id' => $debitAccount->id,
            'debit' => $amount,
            'credit' => 0,
        ]);

        $entry->lines()->create([
            'account_id' => $creditAccount->id,
            'debit' => 0,
            'credit' => $amount,
        ]);

        return $entry;
    }

    private function generateEntryNumber(): string
    {
        return 'JRN-'.now()->format('Ymd').'-'.Str::padLeft((string) random_int(0, 9999), 4, '0');
    }
}
