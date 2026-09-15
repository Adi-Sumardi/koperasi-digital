<?php

declare(strict_types=1);

namespace App\Listeners\Accounting;

use App\Events\Loans\LoanInstallmentRepaidEvent;
use App\Services\Accounting\JournalPostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class PostLoanRepaymentJournalListener implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'financial-ledger';

    public int $tries = 5;

    public function __construct(
        private readonly JournalPostingService $journalService
    ) {}

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(LoanInstallmentRepaidEvent $event): void
    {
        DB::transaction(fn () => $this->journalService->recordLoanRepayment($event->installment));
    }
}
