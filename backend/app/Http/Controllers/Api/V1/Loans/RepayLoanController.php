<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\RepayLoanInstallmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loans\RepayLoanRequest;
use App\Http\Resources\LoanInstallmentResource;
use App\Models\Loan;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class RepayLoanController extends Controller
{
    public function __invoke(RepayLoanRequest $request, Loan $loan, RepayLoanInstallmentAction $action): JsonResponse
    {
        $installment = $action->execute($loan);

        return ApiResponse::success(
            data: new LoanInstallmentResource($installment),
            message: 'Pembayaran angsuran berhasil diproses.',
        );
    }
}
