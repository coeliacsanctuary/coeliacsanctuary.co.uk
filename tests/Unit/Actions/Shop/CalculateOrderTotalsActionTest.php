<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\CalculateOrderTotalsAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Enums\Shop\PostageArea;
use App\Enums\Shop\ShippingMethod;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPostageCountry;
use App\Models\Shop\ShopPostagePrice;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class CalculateOrderTotalsActionTest extends TestCase
{
    protected Collection $itemsCollection;

    protected ShopOrder $order;

    protected ShopOrderItem $item;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->order = ShopOrder::query()->create([
            'postage_country_id' => 1,
        ]);
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        $this->variant->update(['weight' => 1]);

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_price' => 300,
            'quantity' => 2,
        ]);

        $this->itemsCollection = $this->callAction(GetOrderItemsAction::class, $this->order)->collection;

        $this->create(ShopPostagePrice::class, [
            'postage_country_area_id' => PostageArea::UK->value,
            'shipping_method_id' => $this->product->shipping_method_id,
            'max_weight' => 5,
            'price' => 150,
        ]);
    }

    #[Test]
    public function itReturnsAnArrayWithTheCorrectKeys(): void
    {
        $keys = ['subtotal', 'postage'];

        $result = $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);

        $this->assertIsArray($result);
        $this->assertArrayHasKeys($keys, $result);
    }

    #[Test]
    public function itCalculatesTheSubtotal(): void
    {
        ['subtotal' => $subtotal] = $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);

        $this->assertEquals(600, $subtotal);
    }

    #[Test]
    public function itCalculatesThePostagePrice(): void
    {
        ['postage' => $postage] = $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);

        $this->assertEquals(150, $postage);
    }

    #[Test]
    public function itReturnsAPostagePriceForAGivenPostageCountry(): void
    {
        $this->create(ShopPostagePrice::class, [
            'postage_country_area_id' => PostageArea::EUROPE->value,
            'shipping_method_id' => $this->product->shipping_method_id,
            'max_weight' => 5,
            'price' => 300,
        ]);

        $country = $this->create(ShopPostageCountry::class, [
            'postage_area_id' => PostageArea::EUROPE->value,
        ]);

        $this->order->update(['postage_country_id' => $country->id]);

        ['postage' => $price] = $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);

        $this->assertEquals(300, $price);
    }

    #[Test]
    public function itReturnsAPostagePriceForTheWeightOfTheItems(): void
    {
        $this->create(ShopPostagePrice::class, [
            'postage_country_area_id' => PostageArea::UK->value,
            'shipping_method_id' => $this->product->shipping_method_id,
            'max_weight' => 2,
            'price' => 100,
        ]);

        ['postage' => $price] = $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);

        $this->assertEquals(100, $price);
    }

    #[Test]
    public function itReturnsThePostagePriceForTheLargestShippingMethod(): void
    {
        $this->create(ShopPostagePrice::class, [
            'postage_country_area_id' => PostageArea::UK->value,
            'shipping_method_id' => ShippingMethod::LARGE_PARCEL->value,
            'max_weight' => 50,
            'price' => 300,
        ]);

        $product = $this->build(ShopProduct::class)
            ->state(fn () => [
                'shipping_method_id' => ShippingMethod::LARGE_PARCEL->value,
            ])
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->has($this->build(ShopPrice::class), 'prices')
            ->create();

        $this->callAction(AddProductToBasketAction::class, $this->order, $product, $product->variants[0], 1);

        $this->itemsCollection = $this->callAction(GetOrderItemsAction::class, $this->order)->collection;

        ['postage' => $price] = $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);

        $this->assertEquals(300, $price);
    }

    #[Test]
    public function itThrowsAnExceptionIfThePostagePriceCantBeCalculated(): void
    {
        $this->callAction(AddProductToBasketAction::class, $this->order, $this->product, $this->variant, 10);
        $this->itemsCollection = $this->callAction(GetOrderItemsAction::class, $this->order)->collection;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not calculate postage');

        $this->callAction(CalculateOrderTotalsAction::class, $this->itemsCollection, $this->order->postageCountry);
    }
}
