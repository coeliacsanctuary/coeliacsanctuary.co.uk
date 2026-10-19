<?php

declare(strict_types=1);

namespace Tests\Unit\Events\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Listeners\Shop\SendMyTravelCardsInvitationIfRequired;
use App\Listeners\Shop\SendOrderConfirmationMails;
use App\Listeners\Shop\SubscribeToNewsletterIfRequired;
use App\Listeners\Shop\SyncProductsToGoogleMerchantOnOrderPaid;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderPaidEventTest extends TestCase
{
    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->withAdminUser();

        $customer = $this->create(ShopCustomer::class);

        $this->order = $this->build(ShopOrder::class)
            ->asPaid($customer)
            ->create(['has_digital_items' => true]);
    }

    #[Test]
    public function itIsHandledByTheSendOrderConfirmationMailsListener(): void
    {
        $this->mock(SendOrderConfirmationMails::class)
            ->shouldReceive('handle')
            ->once();

        OrderPaidEvent::dispatch($this->order);
    }

    #[Test]
    public function itIsHandledByTheSendMyTravelCardsInvitationIfRequiredListener(): void
    {
        $this->mock(SendMyTravelCardsInvitationIfRequired::class)
            ->shouldReceive('handle')
            ->once();

        OrderPaidEvent::dispatch($this->order);
    }

    #[Test]
    public function itIsHandledByTheSubscribeToNewsletterIfRequiredListener(): void
    {
        $this->mock(SubscribeToNewsletterIfRequired::class)
            ->shouldReceive('handle')
            ->once();

        OrderPaidEvent::dispatch($this->order);
    }

    #[Test]
    public function itIsHandledByTheSyncProductsToGoogleMerchantOnOrderPaidListener(): void
    {
        $this->mock(SyncProductsToGoogleMerchantOnOrderPaid::class)
            ->shouldReceive('handle')
            ->once();

        OrderPaidEvent::dispatch($this->order);
    }
}
