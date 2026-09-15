<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\ApplyLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loans\ApplyLoanRequest;
use App\Http\Resources\LoanApplicationResource;
use App\Models\LoanProduct;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApplyLoanController extends Controller
{
    public function __invoke(ApplyLoanRequest $request, ApplyLoanAction $action): JsonResponse
    {
        $product = LoanProduct::findOrFail($request->validated('loan_product_id'));

        $application = $action->execute(
            member: $request->user()->member,
            product: $product,
            amount: (float) $request->validated('amount'),
            tenorMonths: (int) $request->validated('tenor_months'),
            purpose: $request->validated('purpose'),
            guaranteeType: $request->validated('guarantee_type'),
        );

        return ApiResponse::success(
            data: new LoanApplicationResource($application),
            message: 'Pengajuan pinjaman berhasil dikirimkan.',
            code: Response::HTTP_CREATED,
        );
    }
}
