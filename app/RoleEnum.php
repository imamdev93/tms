<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum RoleEnum: string implements HasLabel
{
    case SUPERADMIN = 'SUPERADMIN';
    case ADMIN = 'ADMIN';
    case PENGURUS = 'PENGURUS';
    case PELATIH = 'PELATIH';
    case ATLET = 'ATLET';
    case WASIT = 'WASIT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUPERADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::PENGURUS => 'Pengurus',
            self::PELATIH => 'Pelatih',
            self::ATLET => 'Atlet',
            self::WASIT => 'Wasit',
        };
    }
}
