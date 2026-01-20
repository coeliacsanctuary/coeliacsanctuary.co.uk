<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProductAddOn;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\AddProductToBasketAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
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
    public function itAssociatesTheAddOnWithTheOrderItemRowIfRequired(): void
    {
        $addOn = $this->build(ShopProductAddOn::class)
            ->forProduct($this->product)
            ->has($this->build(ShopPrice::class), 'prices')
            ->create();

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1, true);

        /** @var ShopOrderItem $item */
        $item = ShopOrderItem::query()->first();

        $this->assertNotNull($item->product_add_on_id);
        $this->assertNotNull($item->product_add_on_title);
        $this->assertNotNull($item->product_add_on_price);

        $this->assertTrue($item->addOn->is($addOn));
        $this->assertEquals($addOn->name, $item->product_add_on_title);
        $this->assertEquals($addOn->currentPrice, $item->product_add_on_price);
    }

    #[Test]
    public function itCanRemoveTheAddOnIfTheItemIsInTheBasketWithAnAddOnAndAddOnIsFalse(): void
    {
        $item = $this->order->items()->create([
            'product_id' => $this->product->id,
            'product_title' => $this->product->title,
            'product_variant_id' => $this->variant->id,
            'product_price' => $this->product->current_price,
            'quantity' => 1,
            'product_add_on_id' => 123,
            'product_add_on_title' => 'Foo',
            'product_add_on_price' => 100,
        ]);

        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 1, false);

        $item->refresh();

        $this->assertNull($item->product_add_on_id);
        $this->assertNull($item->product_add_on_title);
        $this->assertNull($item->product_add_on_price);
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
