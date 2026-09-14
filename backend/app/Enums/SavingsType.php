<?php

declare(strict_types=1);

namespace App\Enums;

enum SavingsType: string
{
    case POKOK = 'pokok';
    case WAJIB = 'wajib';
    case SUKARELA = 'sukarela';

    public function isWithdrawable(): bool
    {
        return $this === self::SUKARELA;
    }

    public function label(): string
    {
        return match ($this) {
            self::POKOK => 'Simpanan Pokok',
            self::WAJIB => 'Simpanan Wajib',
            self::SUKARELA => 'Simpanan Sukarela',
        };
    }
}
