<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum StatusEnum: string implements HasLabel
{
    case ACTIVE = 'ACTIVE';
    case INACTIVE = 'INACTIVE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif',
            self::INACTIVE => 'Tidak Aktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
        };
    }
}
