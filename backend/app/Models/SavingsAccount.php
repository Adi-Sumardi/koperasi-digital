<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SavingsAccountStatus;
use App\Enums\SavingsType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['member_id', 'account_number', 'type', 'balance', 'status'])]
class SavingsAccount extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'type' => SavingsType::class,
            'status' => SavingsAccountStatus::class,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class);
    }
}
