<?php

declare(strict_types=1);

namespace App\Ai\Concerns;

use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Money\Money;

trait FormatTravelCard
{
    protected function formatTravelCard(ShopProduct $product): array
    {
        return [
            'title' => $product->title,
            'description' => $product->description,
            'link' => $product->absolute_link,
            'price' => Helpers::formatMoney(Money::GBP($product->currentPrice)),
            'type' => $product->categories->first()?->title === 'Coeliac Gluten Free Travel Cards' ? 'Standard' : 'Coeliac+',
            'rating' => [
                'average' => $product->averageRating,
                'count' => $product->reviews->count(),
            ],
        ];
    }
}
