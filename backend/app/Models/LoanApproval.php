<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanApprovalDecision;
use App\Exceptions\FinancialImmutableException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['loan_application_id', 'approver_id', 'decision', 'notes'])]
class LoanApproval extends Model
{
    use HasFactory, HasUuids;

    public const CREATED_AT = 'decided_at';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'decision' => LoanApprovalDecision::class,
            'decided_at' => 'immutable_datetime',
        ];
    }

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Keputusan persetujuan berjenjang adalah jejak audit; tidak boleh diubah/dihapus.
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new FinancialImmutableException('Keputusan persetujuan pinjaman tidak boleh diedit.');
        });

        static::deleting(function () {
            throw new FinancialImmutableException('Keputusan persetujuan pinjaman tidak boleh dihapus.');
        });
    }
}
