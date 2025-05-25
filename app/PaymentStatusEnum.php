<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum PaymentStatusEnum: string implements HasLabel
{
    case PENDING = 'pending';
    case CAPTURE = 'capture';
    case PROCESSING = 'processing';
    case SETTLEMENT = 'settlement';
    case CANCEL = 'cancel';
    case DENY = 'deny';
    case EXPIRE = 'expire';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'Tertunda',
            self::CAPTURE => 'Capture',
            self::PROCESSING => 'Menunggu Pembayaran',
            self::SETTLEMENT => 'Settlement',
            self::CANCEL => 'Dibatalkan',
            self::DENY => 'Tertolak',
            self::EXPIRE => 'Expire',
            self::FAILED => 'Gagal',
            self::COMPLETED => 'Berhasil',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::PENDING => 'primary',
            self::CAPTURE => 'danger',
            self::PROCESSING => 'primary',
            self::SETTLEMENT => 'danger',
            self::CANCEL => 'danger',
            self::DENY => 'danger',
            self::EXPIRE => 'danger',
            self::FAILED => 'danger',
            self::COMPLETED => 'success',
        };
    }
}
