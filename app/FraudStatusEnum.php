<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum FraudStatusEnum: string implements HasLabel
{
    case CHALLENGE = 'challenge';
    case ACCEPT = 'accept';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CHALLENGE => 'Challenge',
            self::ACCEPT => 'Accept',
        };
    }
}
