<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'member_id',
    'ktp_photo_path',
    'selfie_ktp_path',
    'status',
    'rejection_reason',
    'verified_by',
    'verified_at',
])]
class MemberKyc extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'member_kyc';

    protected function casts(): array
    {
        return [
            'status' => KycStatus::class,
            'verified_at' => 'immutable_datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
