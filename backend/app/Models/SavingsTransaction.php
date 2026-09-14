<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SavingsTransactionType;
use App\Exceptions\FinancialImmutableException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['savings_account_id', 'reference_no', 'type', 'amount', 'balance_after', 'notes'])]
class SavingsTransaction extends Model
{
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'type' => SavingsTransactionType::class,
        ];
    }

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class);
    }

    /**
     * Riwayat mutasi finansial adalah jejak audit; tidak boleh diubah/dihapus (rules/security.md §3).
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new FinancialImmutableException('Riwayat mutasi simpanan tidak boleh diedit.');
        });

        static::deleting(function () {
            throw new FinancialImmutableException('Riwayat mutasi simpanan tidak boleh dihapus.');
        });
    }
}
