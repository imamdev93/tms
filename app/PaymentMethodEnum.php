<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethodEnum: string implements HasLabel
{
    case E_WALLET = 'EWALLET';
    case BANK = 'BANK';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::E_WALLET => 'E-Wallet',
            self::BANK => 'Bank',
        };
    }
}
