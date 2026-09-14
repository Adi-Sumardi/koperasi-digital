<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\FinancialImmutableException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['journal_entry_id', 'account_id', 'debit', 'credit', 'notes'])]
class JournalLine extends Model
{
    use HasFactory, HasUuids;

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new FinancialImmutableException('Baris jurnal tidak boleh diedit.');
        });

        static::deleting(function () {
            throw new FinancialImmutableException('Baris jurnal tidak boleh dihapus.');
        });
    }
}
