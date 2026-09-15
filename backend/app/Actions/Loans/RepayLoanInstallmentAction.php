<?php

declare(strict_types=1);

namespace App\Actions\Loans;

use App\Enums\LoanInstallmentStatus;
use App\Enums\LoanStatus;
use App\Events\Loans\LoanInstallmentRepaidEvent;
use App\Exceptions\LoanWorkflowException;
use App\Models\Loan;
use App\Models\LoanInstallment;
use Illuminate\Support\Facades\DB;

class RepayLoanInstallmentAction
{
    /**
     * Melunasi angsuran jatuh tempo berikutnya secara penuh (simulasi — belum ada
     * integrasi payroll cut/VA sungguhan).
     */
    public function execute(Loan $loan): LoanInstallment
    {
        return DB::transaction(function () use ($loan) {
            $loan = Loan::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== LoanStatus::ACTIVE) {
                throw new LoanWorkflowException('Pinjaman ini sudah lunas atau tidak lagi aktif.');
            }

            /** @var LoanInstallment|null $installment */
            $installment = $loan->installments()
                ->where('status', LoanInstallmentStatus::UNPAID)
                ->orderBy('installment_number')
                ->lockForUpdate()
                ->first();

            if (! $installment) {
                throw new LoanWorkflowException('Tidak ada angsuran yang perlu dibayar.');
            }

            $installment->update([
                'paid_amount' => $installment->total_installment,
                'status' => LoanInstallmentStatus::PAID,
                'paid_at' => now(),
            ]);

            $loan->decrement('outstanding_balance', (float) $installment->principal_portion);

            $remainingUnpaid = $loan->installments()->where('status', LoanInstallmentStatus::UNPAID)->count();
            if ($remainingUnpaid === 0) {
                $loan->update(['status' => LoanStatus::PAID_OFF]);
            }

            LoanInstallmentRepaidEvent::dispatch($installment->fresh());

            return $installment->fresh();
        });
    }
}
