<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum OrganizationLevelEnum: string implements HasLabel
{
    case PUSAT = 'PUSAT';
    case PROVINSI = 'PROVINSI';
    case KOTA = 'KOTA';
    case DOJANG = 'DOJANG';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PUSAT => 'Pusat',
            self::PROVINSI => 'Provinsi',
            self::KOTA => 'Kota',
            self::DOJANG => 'Dojang',
        };
    }
}
