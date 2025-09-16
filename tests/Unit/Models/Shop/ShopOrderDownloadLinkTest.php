<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ShopOrderDownloadLinkTest extends TestCase
{
    #[Test]
    public function itThrowsAnExceptionIfTheRelatedOrderIsInBasketState(): void
    {
        $order = $this->build(ShopOrder::class)->asBasket()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create download link for order in basket');

        $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);
    }

    #[Test]
    public function itThrowsAnExceptionIfTheRelatedOrderIsInPendingState(): void
    {
        $order = $this->build(ShopOrder::class)->asPending()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create download link for order in pending state');

        $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);
    }

    #[Test]
    public function itThrowsAnExceptionIfTheRelatedOrderIsCancelled(): void
    {
        $order = $this->build(ShopOrder::class)->asCancelled()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create download link for order that is cancelled');

        $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);
    }

    #[Test]
    public function itThrowsAnExceptionIfTheOrderDoesntHaveAnyDigitalProducts(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->create([
            'has_digital_products' => false,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot create download link for order without digital products');

        $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);
    }

    #[Test]
    public function itCreatesTheRecord(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $downloadLink = $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);

        $this->assertTrue(Str::isUuid($downloadLink->id));
    }

    #[Test]
    public function itUpdatesOtherRecordsToExpired(): void
    {
        TestTime::freeze();

        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $originalDownloadLink = $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);

        TestTime::addWeeks(2);

        $this->assertFalse($originalDownloadLink->expires_at->isPast());

        $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);

        TestTime::addSecond();

        $this->assertTrue($originalDownloadLink->refresh()->expires_at->isPast());
    }

    #[Test]
    public function itDoesntUpdateTheRecordIfItHasAlreadyExpired(): void
    {
        TestTime::freeze();

        $order = $this->build(ShopOrder::class)->asPaid()->hasDigitalProducts()->create();

        $originalDownloadLink = $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);

        $originalExpiresAt = clone $originalDownloadLink->expires_at;

        TestTime::addMonths(2);

        $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $order->id,
        ]);

        TestTime::addSecond();

        $this->assertTrue($originalDownloadLink->refresh()->expires_at->equalTo($originalExpiresAt));
    }
}
