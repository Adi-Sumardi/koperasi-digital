<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\SavingsTransaction;
use Illuminate\Support\Str;

class JournalPostingService
{
    private const KAS_ACCOUNT_CODE = '1-1000';

    private const PIUTANG_PINJAMAN_ACCOUNT_CODE = '1-1200';

    private const PENDAPATAN_BUNGA_ACCOUNT_CODE = '4-1000';

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

        return $this->postEntry(
            description: "Setoran {$account->type->label()} — {$transaction->reference_no}",
            referenceType: 'savings_transaction',
            referenceId: $transaction->id,
            lines: [
                ['code' => self::KAS_ACCOUNT_CODE, 'debit' => (float) $transaction->amount, 'credit' => 0],
                ['code' => self::SAVINGS_LIABILITY_CODES[$account->type->value], 'debit' => 0, 'credit' => (float) $transaction->amount],
            ],
        );
    }

    /**
     * Penarikan simpanan sukarela: Debit Liabilitas Simpanan, Kredit Kas.
     */
    public function recordSavingsWithdrawal(SavingsTransaction $transaction): JournalEntry
    {
        $account = $transaction->savingsAccount;

        return $this->postEntry(
            description: "Penarikan {$account->type->label()} — {$transaction->reference_no}",
            referenceType: 'savings_transaction',
            referenceId: $transaction->id,
            lines: [
                ['code' => self::SAVINGS_LIABILITY_CODES[$account->type->value], 'debit' => (float) $transaction->amount, 'credit' => 0],
                ['code' => self::KAS_ACCOUNT_CODE, 'debit' => 0, 'credit' => (float) $transaction->amount],
            ],
        );
    }

    /**
     * Pencairan pinjaman: Debit Piutang Pinjaman, Kredit Kas.
     */
    public function recordLoanDisbursement(Loan $loan): JournalEntry
    {
        return $this->postEntry(
            description: "Pencairan Pinjaman — {$loan->loan_number}",
            referenceType: 'loan',
            referenceId: $loan->id,
            lines: [
                ['code' => self::PIUTANG_PINJAMAN_ACCOUNT_CODE, 'debit' => (float) $loan->principal_amount, 'credit' => 0],
                ['code' => self::KAS_ACCOUNT_CODE, 'debit' => 0, 'credit' => (float) $loan->principal_amount],
            ],
        );
    }

    /**
     * Pembayaran angsuran: Debit Kas (total dibayar), Kredit Piutang Pinjaman (porsi
     * pokok) & Kredit Pendapatan Bunga Pinjaman (porsi bunga).
     */
    public function recordLoanRepayment(LoanInstallment $installment): JournalEntry
    {
        $lines = [
            ['code' => self::KAS_ACCOUNT_CODE, 'debit' => (float) $installment->paid_amount, 'credit' => 0],
            ['code' => self::PIUTANG_PINJAMAN_ACCOUNT_CODE, 'debit' => 0, 'credit' => (float) $installment->principal_portion],
        ];

        if ((float) $installment->interest_portion > 0) {
            $lines[] = ['code' => self::PENDAPATAN_BUNGA_ACCOUNT_CODE, 'debit' => 0, 'credit' => (float) $installment->interest_portion];
        }

        return $this->postEntry(
            description: "Pembayaran Angsuran #{$installment->installment_number} — {$installment->loan->loan_number}",
            referenceType: 'loan_installment',
            referenceId: $installment->id,
            lines: $lines,
        );
    }

    /**
     * @param  array<int, array{code: string, debit: float, credit: float}>  $lines
     */
    private function postEntry(string $description, string $referenceType, string $referenceId, array $lines): JournalEntry
    {
        $entry = JournalEntry::create([
            'entry_number' => $this->generateEntryNumber(),
            'entry_date' => now()->toDateString(),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'is_posted' => true,
        ]);

        // Seluruh baris disisipkan dalam transaksi DB yang sama (lihat pemanggil di
        // listener); trigger keseimbangan debit=kredit divalidasi DEFERRED, saat COMMIT.
        foreach ($lines as $line) {
            $account = ChartOfAccount::where('code', $line['code'])->firstOrFail();

            $entry->lines()->create([
                'account_id' => $account->id,
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ]);
        }

        return $entry;
    }

    private function generateEntryNumber(): string
    {
        return 'JRN-'.now()->format('Ymd').'-'.Str::padLeft((string) random_int(0, 9999), 4, '0');
    }
}
