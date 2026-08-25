<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use App\Models\Shop\ShopOrderItem;
use Illuminate\Support\Collection;
use Worksome\RequestFactories\RequestFactory;

class ReviewMyOrderRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'whereHeard' => ['facebook', 'instagram'],
        ];
    }

    /** @param Collection<int, ShopOrderItem> $products */
    public function forProducts(Collection $products): self
    {
        return $this->state([
            'products' => $products
                ->unique('product_id')
                ->map(fn (ShopOrderItem $item) => [
                    'id' => $item->product_id,
                    'rating' => $this->faker->numberBetween(1, 5),
                    'review' => $this->faker->paragraph,
                ])
                ->values()
                ->toArray(),
        ]);
    }
}
