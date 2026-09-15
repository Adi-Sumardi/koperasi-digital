<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanInterestType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'interest_type',
    'annual_interest_rate',
    'min_tenor_months',
    'max_tenor_months',
    'max_ceiling_amount',
    'is_active',
])]
class LoanProduct extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'interest_type' => LoanInterestType::class,
            'annual_interest_rate' => 'decimal:4',
            'max_ceiling_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function monthlyInterestRate(): float
    {
        return (float) $this->annual_interest_rate / 12;
    }

    public function applications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }
}
