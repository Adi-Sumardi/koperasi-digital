<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencegah eksekusi ganda pada endpoint finansial (rules/api.md §4, rules/security.md §3):
 * request dengan X-Idempotency-Key yang sama dalam 24 jam terakhir akan menerima
 * respons tersimpan tanpa mengeksekusi ulang transaksi.
 */
class EnsureIdempotencyKey
{
    private const LOCK_TTL_SECONDS = 30;

    private const RESPONSE_TTL_HOURS = 24;

    public function handle(Request $request, Closure $next): Response
    {
        $idempotencyKey = $request->header('X-Idempotency-Key');

        if (! $idempotencyKey) {
            return ApiResponse::error(
                message: 'Header X-Idempotency-Key wajib disertakan untuk transaksi finansial.',
                code: 'IDEMPOTENCY_KEY_REQUIRED',
                status: Response::HTTP_BAD_REQUEST,
            );
        }

        $cacheKey = $this->cacheKey($request, $idempotencyKey);
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return response()->json($cached['body'], $cached['status']);
        }

        $lockKey = "{$cacheKey}:lock";

        if (! Cache::add($lockKey, true, now()->addSeconds(self::LOCK_TTL_SECONDS))) {
            return ApiResponse::error(
                message: 'Permintaan dengan idempotency key ini sedang diproses.',
                code: 'IDEMPOTENCY_KEY_IN_PROGRESS',
                status: Response::HTTP_CONFLICT,
            );
        }

        try {
            $response = $next($request);

            // Hanya respons sukses yang disimpan — kegagalan (validasi, saldo tidak
            // cukup, dll.) tidak boleh mengunci key ini agar percobaan ulang dengan
            // data yang diperbaiki tidak diblokir permanen.
            if ($response->isSuccessful()) {
                Cache::put($cacheKey, [
                    'status' => $response->getStatusCode(),
                    'body' => json_decode($response->getContent(), true),
                ], now()->addHours(self::RESPONSE_TTL_HOURS));
            }

            return $response;
        } finally {
            Cache::forget($lockKey);
        }
    }

    private function cacheKey(Request $request, string $idempotencyKey): string
    {
        $userId = $request->user()?->id ?? 'guest';

        return "idempotency:{$userId}:{$request->method()}:{$request->path()}:{$idempotencyKey}";
    }
}
