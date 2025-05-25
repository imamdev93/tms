<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum RegistrationTypeEnum: string implements HasLabel
{
    case PENGURUS = 'PENGURUS';
    case PELATIH = 'PELATIH';
    case ATLET = 'ATLET';
    case WASIT = 'WASIT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENGURUS => 'Pengurus',
            self::PELATIH => 'Pelatih',
            self::ATLET => 'Atlet',
            self::WASIT => 'Wasit',
        };
    }
}
