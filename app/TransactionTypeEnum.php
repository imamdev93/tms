<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum TransactionTypeEnum: string implements HasLabel
{
    case NEW = 'NEW';
    case RENEW = 'RENEW';
    case EVENT = 'EVENT';
    case MONTHLY_FEE_ATLET = 'MONTHLY_FEE_ATLET';
    case MONTHLY_FEE_PENGURUS = 'MONTHLY_FEE_PENGURUS';
    case MONTHLY_FEE_WASIT = 'MONTHLY_FEE_WASIT';
    case MONTHLY_FEE_PELATIH = 'MONTHLY_FEE_PELATIH';
    case TOURNAMENT = 'TOURNAMENT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEW => 'Member Baru',
            self::RENEW => 'Perpanjang',
            self::EVENT => 'Kegiatan',
            self::MONTHLY_FEE_ATLET => 'Iuran Bulanan Atlet',
            self::MONTHLY_FEE_PENGURUS => 'Iuran Bulanan Pengurus',
            self::MONTHLY_FEE_WASIT => 'Iuran Bulanan Wasit',
            self::MONTHLY_FEE_PELATIH => 'Iuran Bulanan Pelatih',
            self::TOURNAMENT => 'Turnamen',
        };
    }
}
