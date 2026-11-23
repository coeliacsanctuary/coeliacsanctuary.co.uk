<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\ShipOrderAction;
use App\Enums\Shop\OrderState;
use App\Events\Shop\OrderShippedEvent;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ShipOrderActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    #[Test]
    public function itThrowsAnExceptionIfTheOrderIsntInReadyState(): void
    {
        $order = $this->build(ShopOrder::class)->asCompleted()->create();

        $this->expectException(RuntimeException::class);

        $this->callAction(ShipOrderAction::class, $order);
    }

    #[Test]
    public function itUpdatesTheOrderToShipped(): void
    {
        $order = $this->build(ShopOrder::class)->asReady()->create();

        $this->assertEquals(OrderState::READY, $order->state_id);

        $this->callAction(ShipOrderAction::class, $order);

        $this->assertEquals(OrderState::SHIPPED, $order->fresh()->state_id);
    }

    #[Test]
    public function itSetsTheShippedAtTimestamp(): void
    {
        TestTime::freeze();

        $order = $this->build(ShopOrder::class)->asReady()->create();

        $this->assertNull($order->shipped_at);

        $this->callAction(ShipOrderAction::class, $order);

        $this->assertNotNull($order->refresh()->shipped_at);
        $this->assertTrue(now()->isSameSecond($order->shipped_at));
    }

    #[Test]
    public function itDispatchesAnOrderShippedEvent(): void
    {
        $order = $this->build(ShopOrder::class)->asReady()->create();

        $this->callAction(ShipOrderAction::class, $order);

        Event::assertDispatched(OrderShippedEvent::class);
    }
}
