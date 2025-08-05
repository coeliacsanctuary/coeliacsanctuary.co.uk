<?php

declare(strict_types=1);

namespace App\Enums\EatingOut;

enum EateryType: int
{
    case EATERY = 1;
    case ATTRACTION = 2;
    case HOTEL = 3;

    public function color(): string
    {
        return match ($this) {
            self::HOTEL => '#DBBC25',
            self::ATTRACTION => '#29719f',
            self::EATERY => '#80CCFC',
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::HOTEL => 'Hotel',
            self::ATTRACTION => 'Attraction',
            self::EATERY => 'Eatery',
        };
    }
}
