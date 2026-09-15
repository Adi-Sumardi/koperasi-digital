<?php

declare(strict_types=1);

namespace App\Enums;

enum MemberStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case RESIGNED = 'resigned';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Aktivasi',
            self::ACTIVE => 'Aktif',
            self::RESIGNED => 'Mengundurkan Diri',
            self::SUSPENDED => 'Ditangguhkan',
        };
    }
}
