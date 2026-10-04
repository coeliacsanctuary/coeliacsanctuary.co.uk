<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Actions\Shop\ReconcileBasketItemsAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopOrderItemTest extends TestCase
{
    #[Test]
    public function itCanGetTheOrder(): void
    {
        $order = $this->create(ShopOrder::class);

        $item = $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->create();

        $this->assertInstanceOf(ShopOrder::class, $item->order);
    }

    #[Test]
    public function itBelongsToAProduct(): void
    {
        $product = $this->create(ShopProduct::class);

        $item = $this->build(ShopOrderItem::class)
            ->inProduct($product)
            ->create();

        $this->assertInstanceOf(ShopProduct::class, $item->refresh()->product()->withoutGlobalScopes()->first());
    }

    #[Test]
    public function itBelongsToAVariant(): void
    {
        $product = $this->create(ShopProductVariant::class);

        $item = $this->build(ShopOrderItem::class)
            ->inVariant($product)
            ->create();

        $this->assertInstanceOf(ShopProductVariant::class, $item->refresh()->variant);
    }

    #[Test]
    public function itReconcilesTheBasketWhenTheItemIsCreated(): void
    {
        $order = $this->create(ShopOrder::class);

        $this->expectAction(ReconcileBasketItemsAction::class, [function (ShopOrder $basket) use ($order) {
            $this->assertTrue($basket->is($order));

            return true;
        }]);

        $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->create();
    }

    #[Test]
    public function itReconcilesTheBasketWhenTheItemIsUpdated(): void
    {
        $order = $this->create(ShopOrder::class);

        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->create();

        $this->expectAction(ReconcileBasketItemsAction::class, [function (ShopOrder $basket) use ($order) {
            $this->assertTrue($basket->is($order));

            return true;
        }]);

        $item->update(['quantity' => 5]);
    }

    #[Test]
    public function itReconcilesTheBasketWhenTheItemIsDeleted(): void
    {
        $order = $this->create(ShopOrder::class);

        /** @var ShopOrderItem $item */
        $item = $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->create();

        $this->expectAction(ReconcileBasketItemsAction::class, [function (ShopOrder $basket) use ($order) {
            $this->assertTrue($basket->is($order));

            return true;
        }]);

        $item->delete();
    }
}
