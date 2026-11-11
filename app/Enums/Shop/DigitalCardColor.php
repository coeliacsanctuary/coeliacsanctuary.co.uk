<?php

declare(strict_types=1);

namespace App\Enums\Shop;

enum DigitalCardColor: string
{
    case GREEN = 'green';
    case RED = 'red';

    public function color(): string
    {
        return match ($this) {
            self::GREEN => '#00e800',
            self::RED => '#ff0000',
        };
    }
}
