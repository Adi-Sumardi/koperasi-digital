<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MemberStatus;
use App\Support\Nik;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

#[Fillable([
    'user_id',
    'full_name',
    'nik',
    'employee_nip',
    'department',
    'bank_name',
    'bank_account_number',
    'monthly_salary',
    'status',
    'member_number',
    'joined_at',
])]
#[Hidden(['nik', 'nik_hash', 'bank_account_number'])]
class Member extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'bank_account_number' => 'encrypted',
            'monthly_salary' => 'decimal:2',
            'status' => MemberStatus::class,
            'joined_at' => 'immutable_datetime',
        ];
    }

    /**
     * NIK dienkripsi untuk penyimpanan, dengan `nik_hash` (HMAC deterministik)
     * sebagai pasangan untuk validasi keunikan & pencarian (lihat App\Support\Nik).
     */
    protected function nik(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : Crypt::decryptString($value),
            set: fn (string $value) => [
                'nik' => Crypt::encryptString($value),
                'nik_hash' => Nik::hash($value),
            ],
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(MemberKyc::class);
    }

    public function isActive(): bool
    {
        return $this->status === MemberStatus::ACTIVE;
    }
}
