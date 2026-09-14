<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $member = $request->user()->member()->with('kyc')->first();

        return ApiResponse::success(
            data: new MemberResource($member),
            message: 'Profil anggota berhasil diambil.',
        );
    }
}
