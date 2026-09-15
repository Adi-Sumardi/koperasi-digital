<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\FinancialImmutableException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['user_id', 'event', 'auditable_type', 'auditable_id', 'ip_address', 'user_agent', 'old_values', 'new_values'])]
class AuditLog extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Jejak audit mutlak (rules/security.md §3): tidak boleh diubah/dihapus.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new FinancialImmutableException('Jejak audit tidak boleh diedit.');
        });

        static::deleting(function () {
            throw new FinancialImmutableException('Jejak audit tidak boleh dihapus.');
        });
    }
}
