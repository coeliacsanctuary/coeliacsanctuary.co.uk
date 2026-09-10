<?php

declare(strict_types=1);

namespace App\Enums\Shop;

enum DigitalCardType: string
{
    case STANDARD = 'standard';
    case PLUS = 'plus';

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Coeliac Gluten Free Travel Card',
            self::PLUS => 'Coeliac+ Other Allergen Travel Card',
        };
    }

    public function categoryId(): int
    {
        return match ($this) {
            self::STANDARD => 1,
            self::PLUS => 11,
        };
    }
}
