<?php

declare(strict_types=1);

namespace App\Support;

class Nik
{
    /**
     * Encrypted NIK values are non-deterministic (random IV per encryption),
     * so a deterministic HMAC digest is stored alongside it to enforce
     * uniqueness and allow lookups without decrypting every row.
     */
    public static function hash(string $nik): string
    {
        return hash_hmac('sha256', $nik, config('app.key'));
    }
}
