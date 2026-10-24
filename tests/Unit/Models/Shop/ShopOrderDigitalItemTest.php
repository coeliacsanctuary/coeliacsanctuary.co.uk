<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Actions\Shop\ReconcileBasketItemsAction;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopOrderDigitalItemTest extends TestCase
{
    #[Test]
    public function itCanGetTheOrder(): void
    {
        $order = $this->create(ShopOrder::class);

        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->create();

        $this->assertInstanceOf(ShopOrder::class, $item->order);
    }

    #[Test]
    public function itBelongsToACard(): void
    {
        $card = $this->create(ShopDigitalCard::class);

        $item = $this->build(ShopOrderDigitalItem::class)
            ->forCard($card)
            ->create();

        $this->assertInstanceOf(ShopDigitalCard::class, $item->refresh()->card);
    }

    #[Test]
    public function itBelongsToAPhysicalOrderItem(): void
    {
        $orderItem = $this->create(ShopOrderItem::class);

        $item = $this->build(ShopOrderDigitalItem::class)
            ->create(['physical_order_item_id' => $orderItem->id]);

        $this->assertInstanceOf(ShopOrderItem::class, $item->refresh()->orderItem);
    }

    #[Test]
    public function itReconcilesTheBasketWhenTheItemIsCreated(): void
    {
        $order = $this->create(ShopOrder::class);

        $this->expectAction(ReconcileBasketItemsAction::class, [function (ShopOrder $basket) use ($order) {
            $this->assertTrue($basket->is($order));

            return true;
        }]);

        $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->create();
    }

    #[Test]
    public function itReconcilesTheBasketWhenTheItemIsUpdated(): void
    {
        $order = $this->create(ShopOrder::class);

        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->create();

        $this->expectAction(ReconcileBasketItemsAction::class, [function (ShopOrder $basket) use ($order) {
            $this->assertTrue($basket->is($order));

            return true;
        }]);

        $item->update(['digital_card_price' => 1000]);
    }

    #[Test]
    public function itReconcilesTheBasketWhenTheItemIsDeleted(): void
    {
        $order = $this->create(ShopOrder::class);

        /** @var ShopOrderDigitalItem $item */
        $item = $this->build(ShopOrderDigitalItem::class)
            ->inOrder($order)
            ->create();

        $this->expectAction(ReconcileBasketItemsAction::class, [function (ShopOrder $basket) use ($order) {
            $this->assertTrue($basket->is($order));

            return true;
        }]);

        $item->delete();
    }
}
