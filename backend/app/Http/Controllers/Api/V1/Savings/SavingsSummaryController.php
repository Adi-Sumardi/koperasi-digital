<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Savings;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavingsAccountResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavingsSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $accounts = $request->user()->member->savingsAccounts;

        return ApiResponse::success(
            data: SavingsAccountResource::collection($accounts),
            message: 'Ringkasan saldo simpanan berhasil diambil.',
        );
    }
}
