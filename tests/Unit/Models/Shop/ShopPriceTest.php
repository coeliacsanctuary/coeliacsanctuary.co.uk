<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopPriceTest extends TestCase
{
    #[Test]
    public function itBelongsToAProduct(): void
    {
        $price = $this->create(ShopPrice::class);

        $this->assertInstanceOf(ShopProduct::class, $price->product()->withoutGlobalScopes()->first());
    }

    #[Test]
    public function itBelongsToAVariant(): void
    {
        $price = $this->create(ShopPrice::class);

        $this->assertInstanceOf(ShopProductVariant::class, $price->variant()->withoutGlobalScopes()->first());
    }
}
