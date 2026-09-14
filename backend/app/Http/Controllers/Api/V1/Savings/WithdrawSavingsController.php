<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Savings;

use App\Actions\Savings\WithdrawSavingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Savings\WithdrawSavingsRequest;
use App\Http\Resources\SavingsTransactionResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class WithdrawSavingsController extends Controller
{
    public function __invoke(WithdrawSavingsRequest $request, WithdrawSavingsAction $action): JsonResponse
    {
        $transaction = $action->execute(
            member: $request->user()->member,
            amount: (float) $request->validated('amount'),
        );

        return ApiResponse::success(
            data: new SavingsTransactionResource($transaction),
            message: 'Penarikan simpanan sukarela berhasil diproses.',
        );
    }
}
