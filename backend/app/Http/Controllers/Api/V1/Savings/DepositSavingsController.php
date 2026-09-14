<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Savings;

use App\Actions\Savings\DepositSavingsAction;
use App\Enums\SavingsType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\DepositSavingsRequest;
use App\Http\Resources\SavingsTransactionResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DepositSavingsController extends Controller
{
    public function __invoke(DepositSavingsRequest $request, DepositSavingsAction $action): JsonResponse
    {
        $transaction = $action->execute(
            member: $request->user()->member,
            type: SavingsType::from($request->validated('type')),
            amount: (float) $request->validated('amount'),
        );

        return ApiResponse::success(
            data: new SavingsTransactionResource($transaction),
            message: 'Setoran simpanan berhasil dicatat.',
            code: Response::HTTP_CREATED,
        );
    }
}
