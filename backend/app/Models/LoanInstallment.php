<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LoanInstallmentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'loan_id',
    'installment_number',
    'due_date',
    'principal_portion',
    'interest_portion',
    'total_installment',
    'paid_amount',
    'status',
    'paid_at',
])]
class LoanInstallment extends Model
{
    use HasFactory, HasUuids;

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'principal_portion' => 'decimal:2',
            'interest_portion' => 'decimal:2',
            'total_installment' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'status' => LoanInstallmentStatus::class,
            'paid_at' => 'immutable_datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
