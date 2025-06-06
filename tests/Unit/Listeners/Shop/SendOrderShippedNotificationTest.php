<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Events\Shop\OrderShippedEvent;
use App\Listeners\Shop\SendOrderShippedNotification;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Notifications\Shop\OrderShippedNotification;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendOrderShippedNotificationTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withAdminUser();
        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->customer = $this->create(ShopCustomer::class);

        $product = ShopProduct::query()
            ->whereHas('media')
            ->firstOrFail();

        $this->order = $this->build(ShopOrder::class)
            ->asShipped($this->customer)
            ->create();

        $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->add($product->variants->first())
            ->create();

        Notification::fake();
        Mail::fake();
    }

    #[Test]
    public function itSendsACNotificationToTheCustomer(): void
    {
        $event = new OrderShippedEvent($this->order);

        app(SendOrderShippedNotification::class)->handle($event);

        Notification::assertSentTo($this->customer, OrderShippedNotification::class);
    }
}
