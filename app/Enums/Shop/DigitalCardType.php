<?php

declare(strict_types=1);

namespace App\Enums\Shop;

use App\Models\Shop\ShopCategory;
use Illuminate\Support\Str;
use InvalidArgumentException;

enum DigitalCardType: string
{
    case STANDARD = 'standard';
    case PLUS = 'plus';

    public static function fromRequestPath(string $category): self
    {
        $category = Str::after($category, 'shop/');

        return match ($category) {
            'digital-coeliac-gluten-free-travel-cards' => self::STANDARD,
            //            'plus' => self::PLUS, @todo
            default => throw new InvalidArgumentException('Invalid category'),
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::STANDARD => 'Coeliac Gluten Free Travel Card',
            self::PLUS => 'Coeliac+ Other Allergen Travel Card',
        };
    }

    public function pluralLabel(): string
    {
        return Str::plural($this->label());
    }

    public function pageTitle(): string
    {
        return "Digital {$this->pluralLabel()}";
    }

    public function categoryId(): int
    {
        return match ($this) {
            self::STANDARD => 1,
            self::PLUS => 11,
        };
    }

    public function category(): ShopCategory
    {
        return ShopCategory::query()->findOrFail($this->categoryId());
    }
}
