<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class ShopAddBasketRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'product_id' => 1,
            'variant_id' => 1,
            'quantity' => 1,
        ];
    }
}
