<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\CheckIfBasketHasDigitalProductsAction;
use App\Enums\Shop\ProductVariantType;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddProductToBasketActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create();
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();
    }

    #[Test]
    public function itCreatesAShopOrderItemAgainstTheBasketRecord(): void
    {
        $this->assertDatabaseEmpty(ShopOrderItem::class);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->assertDatabaseCount(ShopOrderItem::class, 1);

        $item = ShopOrderItem::query()->first();

        $this->assertTrue($item->product->is($this->product));
        $this->assertTrue($item->variant->is($this->variant));
        $this->assertEquals(1, $item->quantity);
    }

    #[Test]
    public function itUpdatesTheQuantityOfAnExistingBasketItemIfItIsAlreadyInTheBasket(): void
    {
        $item = $this->order->items()->create([
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'product_variant_id' => $this->variant->id,
            'product_price' => $this->variant->current_price,
            'quantity' => 1,
        ]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->assertDatabaseCount(ShopOrderItem::class, 1);

        $item->refresh();

        $this->assertEquals(2, $item->quantity);
    }

    #[Test]
    public function itDeductsTheQuantityFromTheVariant(): void
    {
        $this->variant->update(['quantity' => 2]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->variant->refresh();

        $this->assertEquals(1, $this->variant->quantity);
    }

    #[Test]
    public function itDoesntDeductTheQuantityFromTheVariantIfTheVariantIsDigitalOnly(): void
    {
        $this->variant->update([
            'quantity' => 2,
            'variant_type' => ProductVariantType::DIGITAL,
        ]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->variant->refresh();

        $this->assertEquals(2, $this->variant->quantity);
    }

    #[Test]
    public function ifAVariantIsABundleThenItWillUpdateTheStockOfTheSibblingPhyisalVariant(): void
    {
        $this->variant->update([
            'quantity' => 2,
            'variant_type' => ProductVariantType::PHYSICAL,
        ]);

        $bundleVariant = $this->build(ShopProductVariant::class)
            ->has($this->build(ShopPrice::class), 'prices')
            ->belongsToProduct($this->product)
            ->isBundle()
            ->create([
                'quantity' => 100,
            ]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $bundleVariant, 1);

        $bundleVariant->refresh();
        $this->variant->refresh();

        $this->assertEquals(100, $bundleVariant->quantity);
        $this->assertEquals(1, $this->variant->quantity);
    }

    #[Test]
    public function itCallsTheCheckIfBasketHasDigitalProductsAction(): void
    {
        $this->mock(CheckIfBasketHasDigitalProductsAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($order) {
                $this->assertTrue($this->order->is($order));

                return true;
            })
            ->once();

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);
    }
}
