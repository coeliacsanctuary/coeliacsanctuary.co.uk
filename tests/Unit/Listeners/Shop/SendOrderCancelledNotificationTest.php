<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Events\Shop\OrderCancelledEvent;
use App\Listeners\Shop\SendOrderCancellationNotification;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Notifications\Shop\OrderCancelledNotification;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendOrderCancelledNotificationTest extends TestCase
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
        $event = new OrderCancelledEvent($this->order);

        app(SendOrderCancellationNotification::class)->handle($event);

        Notification::assertSentTo($this->customer, OrderCancelledNotification::class);
    }
}
