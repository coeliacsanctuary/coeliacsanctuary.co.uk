<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopProductVariantTest extends TestCase
{
    #[Test]
    public function itHasALiveScope(): void
    {
        $this->assertNotEmpty(ShopProductVariant::query()->toBase()->wheres);
    }

    #[Test]
    public function itBelongsToAProduct(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->assertInstanceOf(ShopProduct::class, $variant->product);
    }

    #[Test]
    public function itHasManyPrices(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->count(5)
            ->forVariant($variant)
            ->create();

        $this->assertInstanceOf(Collection::class, $variant->refresh()->prices);
    }

    #[Test]
    public function itCanGetACollectionOfCurrentPrices(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->ended()
            ->create();

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create();

        $this->assertCount(1, $variant->currentPrices());
    }

    #[Test]
    public function itCanGetTheCurrentPrice(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->ended()
            ->create([
                'price' => 200,
            ]);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 100,
            ]);

        $this->assertEquals(100, $variant->currentPrice);
    }

    #[Test]
    public function itReturnsTheOldPrice(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->onSale()
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 200,
            ]);

        $this->assertEquals(200, $variant->oldPrice);
    }

    #[Test]
    public function itReturnsTheOldPriceAsNullIfNotOnSale(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 200,
            ]);

        $this->assertNull($variant->oldPrice);
    }

    #[Test]
    public function itReturnsAPriceObject(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->onSale()
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 200,
            ]);

        $this->assertEquals(['current_price' => '£1.00', 'old_price' => '£2.00'], $variant->price);
    }

    #[Test]
    public function itReturnsAPriceObjectWithoutAnOldPrice(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forVariant($variant)
            ->create([
                'price' => 200,
            ]);

        $this->assertEquals(['current_price' => '£1.00'], $variant->price);
    }
}
