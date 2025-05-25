<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum MemberTypeEnum: string implements HasLabel
{
    case NEW = 'NEW';
    case RENEW = 'RENEW';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NEW => 'Baru',
            self::RENEW => 'Perpanjang',
        };
    }
}
