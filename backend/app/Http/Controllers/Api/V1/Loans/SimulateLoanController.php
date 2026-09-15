<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Actions\Loans\SimulateLoanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Loans\SimulateLoanRequest;
use App\Http\Resources\LoanSimulationResource;
use App\Models\LoanProduct;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class SimulateLoanController extends Controller
{
    public function __invoke(SimulateLoanRequest $request, SimulateLoanAction $action): JsonResponse
    {
        $product = LoanProduct::findOrFail($request->validated('loan_product_id'));

        $result = $action->execute(
            product: $product,
            amount: (float) $request->validated('amount'),
            tenorMonths: (int) $request->validated('tenor_months'),
        );

        return ApiResponse::success(
            data: new LoanSimulationResource($result),
            message: 'Simulasi angsuran pinjaman berhasil dihitung.',
        );
    }
}
