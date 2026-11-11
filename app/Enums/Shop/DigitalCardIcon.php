<?php

declare(strict_types=1);

namespace App\Enums\Shop;

enum DigitalCardIcon: string
{
    case TICK = 'tick';
    case CROSS = 'cross';

    public function icon(): string
    {
        return match ($this) {
            self::TICK => 'tick',
            self::CROSS => 'cross',
        };
    }
}
