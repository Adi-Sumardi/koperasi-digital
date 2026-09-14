<?php

declare(strict_types=1);

namespace App\Models;

use App\Exceptions\FinancialImmutableException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['entry_number', 'entry_date', 'reference_type', 'reference_id', 'description', 'is_posted'])]
class JournalEntry extends Model
{
    use HasFactory, HasUuids;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'is_posted' => 'boolean',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Jurnal akuntansi yang telah diposting tidak boleh diubah atau dihapus (rules/models.md §5).
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new FinancialImmutableException('Jurnal akuntansi yang telah diposting tidak boleh diedit.');
        });

        static::deleting(function () {
            throw new FinancialImmutableException('Jurnal akuntansi tidak boleh dihapus. Gunakan mekanisme jurnal pembalik.');
        });
    }
}
