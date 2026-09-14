<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success(
        JsonResource|array|null $data = null,
        string $message = '',
        int $code = Response::HTTP_OK,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => array_merge([
                'timestamp' => now()->toIso8601String(),
                'api_version' => 'v1',
            ], $meta),
        ], $code);
    }

    public static function error(
        string $message,
        string $code,
        int $status = Response::HTTP_BAD_REQUEST,
        array $errors = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'code' => $code,
        ];

        if ($errors !== []) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
