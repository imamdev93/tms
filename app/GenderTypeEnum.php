<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum GenderTypeEnum: string implements HasLabel
{
    case L = 'L';
    case P = 'P';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::L => 'Laki-Laki',
            self::P => 'Perempuan',
        };
    }
}
