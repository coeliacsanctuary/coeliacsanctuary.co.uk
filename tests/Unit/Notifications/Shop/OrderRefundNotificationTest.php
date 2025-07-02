<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Shop;

use App\Models\Shop\ShopPaymentRefund;
use App\Notifications\Shop\OrderRefundNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Notification;
use Money\Money;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class OrderRefundNotificationTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->customer = $this->create(ShopCustomer::class);

        $product = ShopProduct::query()
            ->whereHas('media')
            ->firstOrFail();

        $this->order = $this->build(ShopOrder::class)
            ->asShipped($this->customer)
            ->create();

        $this->create(ShopPaymentRefund::class, [
            'order_id' => $this->order->id,
            'payment_id' => $this->order->payment->id,
        ]);

        $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->add($product->variants->first())
            ->create();

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheOrderData(callable $closure): void
    {
        $this->customer->notify(new OrderRefundNotification($this->order->refunds->first(), 'this is the refund reason'));

        Notification::assertSentTo(
            $this->customer,
            OrderRefundNotification::class,
            function (OrderRefundNotification $notification) use ($closure): bool {
                $mail = $notification->toMail($this->customer);
                $content = $mail->render();

                $closure($this, $mail, $content);

                return true;
            }
        );
    }

    public static function mailDataProvider(): array
    {
        return [
            'has the email key' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($message->data()['key'], $emailContent);
            }],
            'has the order date' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(now()->format('d/m/Y'), $emailContent);
            }],
            'has the customer name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->address->name, $emailContent);
            }],
            'has the refund reason' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('this is the refund reason', $emailContent);
            }],
            'has the order number' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString((string) $test->order->order_key, $emailContent);
            }],
            'has the order total' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(Helpers::formatMoney(Money::GBP($test->order->payment->total)), $emailContent);
            }],
            'has the product image' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->items->first()->product->main_image, $emailContent);
            }],
            'has the product link' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->items->first()->product->link, $emailContent);
            }],
            'has the quantity' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->items->first()->quantity . 'X', $emailContent);
            }],
            'has the product title' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->items->first()->product_title, $emailContent);
            }],
            'has the product price' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(Helpers::formatMoney(Money::GBP($test->order->items->first()->product_price)), $emailContent);
            }],
            'has the order subtotal' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(Helpers::formatMoney(Money::GBP($test->order->payment->subtotal)), $emailContent);
            }],
            'has the order postage' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(Helpers::formatMoney(Money::GBP($test->order->payment->postage)), $emailContent);
            }],
            'has the refund amount' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(Helpers::formatMoney(Money::GBP($test->order->payment->refunds->first()->amount)), $emailContent);
            }],
            'has the shipping address' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->address->line_1, $emailContent);
                $test->assertStringContainsString($test->order->address->town, $emailContent);
                $test->assertStringContainsString($test->order->address->postcode, $emailContent);
            }],
        ];
    }
}
