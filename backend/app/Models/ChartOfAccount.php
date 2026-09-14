<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountCategory;
use App\Enums\NormalBalance;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'account_type', 'normal_balance', 'is_active'])]
class ChartOfAccount extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'account_type' => AccountCategory::class,
            'normal_balance' => NormalBalance::class,
            'is_active' => 'boolean',
        ];
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalLine::class, 'account_id');
    }
}
