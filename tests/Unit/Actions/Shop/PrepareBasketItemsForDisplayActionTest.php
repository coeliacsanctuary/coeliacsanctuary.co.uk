<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetOrderDigitalItemsAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Actions\Shop\PrepareBasketItemsForDisplayAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use App\Resources\Shop\ShopOrderItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrepareBasketItemsForDisplayActionTest extends TestCase
{
    protected ShopOrder $basket;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ShopOrder $basket */
        $basket = $this->create(ShopOrder::class);

        $this->basket = $basket;
    }

    protected function item(array $attributes = []): ShopOrderItem
    {
        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)->inOrder($this->basket)->make($attributes);

        return $item;
    }

    protected function digitalItem(array $attributes = []): ShopOrderDigitalItem
    {
        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)->inOrder($this->basket)->make($attributes);

        return $item;
    }

    /**
     * @param  array<int, ShopOrderItem>  $items
     * @param  array<int, ShopOrderDigitalItem>  $digitalItems
     */
    protected function prepare(array $items = [], array $digitalItems = []): array
    {
        $this->expectAction(GetOrderItemsAction::class, [$this->basket], return: ShopOrderItemResource::collection(collect($items)));
        $this->expectAction(GetOrderDigitalItemsAction::class, [$this->basket], return: ShopOrderDigitalItemResource::collection(collect($digitalItems)));

        return $this->callAction(PrepareBasketItemsForDisplayAction::class, $this->basket);
    }

    #[Test]
    public function itReturnsTheItemsFromTheGetOrderItemsAction(): void
    {
        $items = $this->prepare([$this->item()])['items'];

        $this->assertInstanceOf(AnonymousResourceCollection::class, $items);
        $this->assertCount(1, $items);
        $this->assertInstanceOf(ShopOrderItemResource::class, $items->collection->first());
    }

    #[Test]
    public function itReturnsTheDigitalItemsFromTheGetOrderDigitalItemsAction(): void
    {
        $digitalItems = $this->prepare(digitalItems: [$this->digitalItem()])['digitalItems'];

        $this->assertInstanceOf(AnonymousResourceCollection::class, $digitalItems);
        $this->assertCount(1, $digitalItems);
        $this->assertInstanceOf(ShopOrderDigitalItemResource::class, $digitalItems->collection->first());
    }

    #[Test]
    public function itReturnsAZeroSubtotalForAnEmptyBasket(): void
    {
        $this->assertSame('£0.00', $this->prepare()['subtotal']);
    }

    #[Test]
    public function itSumsTheItemPricesIntoTheSubtotal(): void
    {
        $items = [
            $this->item(['product_price' => 500, 'quantity' => 1]),
            $this->item(['product_price' => 250, 'quantity' => 1]),
        ];

        $this->assertSame('£7.50', $this->prepare($items)['subtotal']);
    }

    #[Test]
    public function itMultipliesEachItemPriceByItsQuantity(): void
    {
        $items = [$this->item(['product_price' => 500, 'quantity' => 3])];

        $this->assertSame('£15.00', $this->prepare($items)['subtotal']);
    }


    #[Test]
    public function itIncludesTheDigitalItemPricesInTheSubtotal(): void
    {
        $digitalItems = [
            $this->digitalItem(['digital_card_price' => 300]),
            $this->digitalItem(['digital_card_price' => 250]),
        ];

        $this->assertSame('£5.50', $this->prepare(digitalItems: $digitalItems)['subtotal']);
    }

    #[Test]
    public function itCombinesTheItemAndDigitalItemPricesIntoTheSubtotal(): void
    {
        $items = [$this->item(['product_price' => 500, 'quantity' => 2])];
        $digitalItems = [$this->digitalItem(['digital_card_price' => 300])];

        $this->assertSame('£13.00', $this->prepare($items, $digitalItems)['subtotal']);
    }
}
