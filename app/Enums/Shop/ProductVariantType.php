<?php

declare(strict_types=1);

namespace App\Enums\Shop;

enum ProductVariantType: string
{
    case PHYSICAL = 'physical';
    case DIGITAL = 'digital';
    case BUNDLE = 'bundle';

    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL => 'Physical Product Only',
            self::DIGITAL => 'Digital Download Only',
            self::BUNDLE => 'Physical and Digital Bundle',
        };
    }
}
