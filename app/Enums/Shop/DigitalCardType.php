<?php

declare(strict_types=1);

namespace App\Enums\Shop;

use InvalidArgumentException;

enum DigitalCardType: int
{
    case STANDARD = 1;
    case PLUS = 2;

    public function name(): string
    {
        return match ($this) {
            self::STANDARD => 'Digital Coeliac Gluten Free Travel Cards',
            self::PLUS => 'Digital Coeliac+ Other Allergen Travel Cards',
        };
    }

    public function route(): string
    {
        return match ($this) {
            self::STANDARD => route('shop.digital-standard-cards'),
            default => throw new InvalidArgumentException('Invalid category'),
        };
    }
}
