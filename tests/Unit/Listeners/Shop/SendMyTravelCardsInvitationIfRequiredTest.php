<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use App\Enums\Shop\OrderState;
use App\Listeners\Shop\SendMyTravelCardsInvitationIfRequired;
use App\Notifications\Shop\MyTravelCards\MyTravelCardsInvitationNotification;
use PHPUnit\Framework\Attributes\Test;
use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendMyTravelCardsInvitationIfRequiredTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->customer = $this->create(ShopCustomer::class);

        $this->order = $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create([
                'has_digital_items' => true,
            ]);

        Notification::fake();
        Mail::fake();
    }

    #[Test]
    public function itSendsAInvitationEmailToTheCustomer(): void
    {
        $event = new OrderPaidEvent($this->order);

        app(SendMyTravelCardsInvitationIfRequired::class)->handle($event);

        Notification::assertSentTo($this->customer, MyTravelCardsInvitationNotification::class);
    }

    #[Test]
    public function itDoesntSendIfTheOrderIsntDigital(): void
    {
        $this->order->update(['has_digital_items' => false]);

        $event = new OrderPaidEvent($this->order);

        app(SendMyTravelCardsInvitationIfRequired::class)->handle($event);

        Notification::assertNotSentTo($this->customer, MyTravelCardsInvitationNotification::class);
    }

    #[Test]
    public function itMarksADigitalOnlyOrderAsShipped(): void
    {
        $this->order->update(['is_digital_only' => true]);

        $event = new OrderPaidEvent($this->order);

        app(SendMyTravelCardsInvitationIfRequired::class)->handle($event);

        $this->assertEquals(OrderState::SHIPPED, $this->order->refresh()->state_id);
    }

    #[Test]
    public function itDoesntMarkAnOrderWithPhysicalItemsAsShipped(): void
    {
        $event = new OrderPaidEvent($this->order);

        app(SendMyTravelCardsInvitationIfRequired::class)->handle($event);

        $this->assertEquals(OrderState::PAID, $this->order->refresh()->state_id);
    }
}
