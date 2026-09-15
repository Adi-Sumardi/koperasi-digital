<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanApplicationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'member_id',
    'loan_product_id',
    'application_number',
    'amount',
    'tenor_months',
    'purpose',
    'guarantee_type',
    'status',
    'rejection_reason',
    'decided_at',
])]
class LoanApplication extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => LoanApplicationStatus::class,
            'decided_at' => 'immutable_datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LoanApproval::class);
    }

    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class);
    }
}
