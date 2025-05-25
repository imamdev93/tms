<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum RelationEnum: string implements HasLabel
{
    case ORANG_TUA = 'ORANG_TUA';
    case KAKAK = 'KAKAK';
    case ADIK = 'ADIK';
    case LAINNYA = 'LAINNYA';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ORANG_TUA => 'Orang Tua',
            self::KAKAK => 'Kakak',
            self::ADIK => 'Adik',
            self::LAINNYA => 'Lainnya',
        };
    }
}
