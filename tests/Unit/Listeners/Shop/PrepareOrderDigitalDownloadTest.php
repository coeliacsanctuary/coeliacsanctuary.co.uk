<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use App\Enums\Shop\OrderState;
use App\Events\Shop\OrderPaidEvent;
use App\Listeners\Shop\PrepareOrderDigitalDownload;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;
use App\Notifications\Shop\DownloadYourProductsNotification;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class PrepareOrderDigitalDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    #[Test]
    public function itDoesNothingIfTheOrderHasNoDigitalProducts(): void
    {
        $this->assertDatabaseEmpty(ShopOrderDownloadLink::class);

        $order = $this->build(ShopOrder::class)->asPaid()->create([
            'has_digital_products' => false,
        ]);

        $event = new OrderPaidEvent($order);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        $this->assertDatabaseEmpty(ShopOrderDownloadLink::class);

        Notification::assertNothingSent();
    }

    #[Test]
    public function itCreatesADigitalDownloadLinkRecordForTheOrderWhenItContainsDigitalProducts(): void
    {
        $this->assertDatabaseEmpty(ShopOrderDownloadLink::class);

        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $event = new OrderPaidEvent($order);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        $this->assertDatabaseCount(ShopOrderDownloadLink::class, 1);
        $this->assertCount(1, $order->refresh()->downloadLinks);
    }

    #[Test]
    public function itSetsTheExpiresAtOnTheDownloadLinkAsOneMonthAway(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $event = new OrderPaidEvent($order);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        $expiresAt = $order->refresh()->downloadLinks->first()->expires_at;

        $this->assertTrue(now()->addMonth()->isSameDay($expiresAt));
    }

    #[Test]
    public function itNotifiesTheCustomer(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $event = new OrderPaidEvent($order);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        Notification::assertSentTo($order->customer, DownloadYourProductsNotification::class);
    }

    #[Test]
    public function itMarksTheDigitalOrderSentAtTime(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $event = new OrderPaidEvent($order);

        $this->assertNull($order->digital_products_sent_at);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        $this->assertNotNull($order->refresh()->digital_products_sent_at);
    }

    #[Test]
    public function itUpdatesTheOrderStateToCompleteIfItIsDigitalOnly(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->isDigitalOnly()->create();

        $event = new OrderPaidEvent($order);

        $this->assertEquals(OrderState::PAID, $order->state_id);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        $this->assertEquals(OrderState::SHIPPED, $order->refresh()->state_id);
    }

    #[Test]
    public function itDoesntUpdateTheOrderStateToCompleteIfItIsNotDigitalOnly(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $event = new OrderPaidEvent($order);

        $this->assertEquals(OrderState::PAID, $order->state_id);

        app(PrepareOrderDigitalDownload::class)->handle($event);

        $this->assertEquals(OrderState::PAID, $order->refresh()->state_id);
    }
}
