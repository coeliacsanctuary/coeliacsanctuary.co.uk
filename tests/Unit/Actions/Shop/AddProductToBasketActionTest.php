<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddProductToBasketAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
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
            'product_price' => $this->product->current_price,
            'quantity' => 1,
        ]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->assertDatabaseCount(ShopOrderItem::class, 1);

        $item->refresh();

        $this->assertEquals(2, $item->quantity);
    }

    #[Test]
    public function itDoesntUpdateTheQuantityOfAnExistingBasketItemIfItShouldntIncreaseTheQuantity(): void
    {
        $item = $this->order->items()->create([
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'product_variant_id' => $this->variant->id,
            'product_price' => $this->product->current_price,
            'quantity' => 1,
        ]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1, shouldIncreaseQuantity: false);

        $this->assertDatabaseCount(ShopOrderItem::class, 1);

        $item->refresh();

        $this->assertEquals(1, $item->quantity);
    }

    #[Test]
    public function itStillCreatesTheBasketItemIfItShouldntIncreaseTheQuantityAndTheProductIsntInTheBasket(): void
    {
        $this->assertDatabaseEmpty(ShopOrderItem::class);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 2, shouldIncreaseQuantity: false);

        $this->assertDatabaseCount(ShopOrderItem::class, 1);

        $item = ShopOrderItem::query()->first();

        $this->assertEquals(2, $item->quantity);
    }


    #[Test]
    public function itReturnsTheOrderItem(): void
    {
        $item = $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->assertInstanceOf(ShopOrderItem::class, $item);
        $this->assertTrue(ShopOrderItem::query()->first()->is($item));
    }

    #[Test]
    public function itReturnsTheExistingOrderItemIfTheProductIsAlreadyInTheBasket(): void
    {
        $existingItem = $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->inProduct($this->product)
            ->inVariant($this->variant)
            ->create();

        $item = $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->assertDatabaseCount(ShopOrderItem::class, 1);
        $this->assertTrue($existingItem->is($item));
    }

    #[Test]
    public function itDeductsTheQuantityFromTheVariant(): void
    {
        $this->variant->update(['quantity' => 2]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1);

        $this->variant->refresh();

        $this->assertEquals(1, $this->variant->quantity);
    }
}
