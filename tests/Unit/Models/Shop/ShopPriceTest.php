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
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function itBelongsToAProduct(): void
    {
        $product = $this->build(ShopProduct::class)->has($this->build(ShopProductVariant::class), 'variants')->create();
        $price = $this->build(ShopPrice::class)->forProduct($product)->create();

        $this->assertInstanceOf(ShopProduct::class, $price->product()->withoutGlobalScopes()->first());
        $this->assertTrue($price->product()->withoutGlobalScopes()->first()->is($product));
    }

    #[Test]
    public function itBelongsToAVariant(): void
    {
        $variant = $this->create(ShopProductVariant::class);
        $price = $this->build(ShopPrice::class)->forVariant($variant)->create();

        $this->assertInstanceOf(ShopProductVariant::class, $price->variant()->withoutGlobalScopes()->first());
        $this->assertTrue($price->variant()->withoutGlobalScopes()->first()->is($variant));
    }
}
