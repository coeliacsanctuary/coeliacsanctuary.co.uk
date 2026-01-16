<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopPriceTest extends TestCase
{
    #[Test]
    public function itHasAPurchasable(): void
    {
        $price = $this->create(ShopPrice::class);

        $this->assertInstanceOf(ShopProduct::class, $price->purchasable()->withoutGlobalScopes()->first());
    }
}
