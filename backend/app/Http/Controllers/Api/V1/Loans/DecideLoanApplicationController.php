<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\DecideLoanApplicationAction;
use App\Enums\LoanApprovalDecision;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loans\DecideLoanApplicationRequest;
use App\Http\Resources\LoanApplicationResource;
use App\Models\LoanApplication;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Panel persetujuan berjenjang Pengurus & Bendahara (BRD.md §5.3.3).
 *
 * Catatan: endpoint ini sementara diekspos lewat kanal Sanctum yang sama dengan
 * mobile API karena portal Web Admin (session-based, lihat AGENTS.md) belum
 * dibangun. Perlu dipindahkan ke kanal Web Admin saat backoffice tersedia.
 */
class DecideLoanApplicationController extends Controller
{
    public function __invoke(
        DecideLoanApplicationRequest $request,
        LoanApplication $loanApplication,
        DecideLoanApplicationAction $action,
    ): JsonResponse {
        $application = $action->execute(
            application: $loanApplication,
            approver: $request->user(),
            decision: LoanApprovalDecision::from($request->validated('decision')),
            notes: $request->validated('notes'),
        );

        return ApiResponse::success(
            data: new LoanApplicationResource($application),
            message: 'Keputusan persetujuan berhasil dicatat.',
        );
    }
}
