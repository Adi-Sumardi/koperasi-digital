<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Loans;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoanIndexController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $loans = $request->user()->member->loans()->with('installments')->latest()->get();

        return ApiResponse::success(
            data: LoanResource::collection($loans),
            message: 'Daftar pinjaman berhasil diambil.',
        );
    }
}
