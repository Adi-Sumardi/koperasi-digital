<?php

declare(strict_types=1);

namespace App\Listeners\Accounting;

use App\Events\Loans\LoanDisbursedEvent;
use App\Services\Accounting\JournalPostingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class PostLoanDisbursementJournalListener implements ShouldQueue
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

    public function handle(LoanDisbursedEvent $event): void
    {
        DB::transaction(fn () => $this->journalService->recordLoanDisbursement($event->loan));
    }
}
