<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\CloseBasketAction;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProductVariant;
use RuntimeException;
use Tests\TestCase;

class CloseBasketActionTest extends TestCase
{
    #[Test]
    public function itThrowsAnExceptionIfTheOrderIsntInBasketOrPendingState(): void
    {
        $order = $this->build(ShopOrder::class)->asExpired()->create();

        $this->expectException(RuntimeException::class);

        $this->callAction(CloseBasketAction::class, $order);
    }

    #[Test]
    public function itUpdatesAPendingOrderToExpired(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create();

        $this->assertEquals(OrderState::PENDING, $order->state_id);

        $this->callAction(CloseBasketAction::class, $order);

        $this->assertEquals(OrderState::EXPIRED, $order->fresh()->state_id);
    }

    #[Test]
    public function itUpdatesTheBasketToExpired(): void
    {
        $order = $this->build(ShopOrder::class)->asBasket()->create();

        $this->assertEquals(OrderState::BASKET, $order->state_id);

        $this->callAction(CloseBasketAction::class, $order);

        $this->assertEquals(OrderState::EXPIRED, $order->fresh()->state_id);
    }

    #[Test]
    public function itPutsTheOrderItemsBackIntoStock(): void
    {
        $this->withCategoriesAndProducts(1, 1);

        $order = $this->build(ShopOrder::class)->asBasket()->create();
        $variant = ShopProductVariant::query()->first();
        $existingQuantity = $variant->quantity;

        $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->add($variant, 5)
            ->create();

        $this->callAction(CloseBasketAction::class, $order);

        $this->assertEquals($existingQuantity + 5, $variant->fresh()->quantity);
    }
}
