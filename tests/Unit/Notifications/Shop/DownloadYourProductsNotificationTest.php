<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Shop;

use App\Enums\Shop\ProductVariantType;
use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Notifications\Shop\DownloadYourProductsNotification;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class DownloadYourProductsNotificationTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected ShopOrderDownloadLink $downloadLink;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(1, 1);

        $this->customer = $this->create(ShopCustomer::class);

        $product = ShopProduct::query()
            ->whereHas('media')
            ->firstOrFail();

        $variant = $product->variants->first();

        $variant->update([
            'variant_type' => ProductVariantType::DIGITAL,
        ]);

        $this->order = $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->hasDigitalProducts()
            ->create();

        $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->add($variant)
            ->create();

        $this->downloadLink = $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $this->order->id,
        ]);

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheOrderDate(callable $closure): void
    {
        $this->customer->notify(new DownloadYourProductsNotification($this->downloadLink));

        Notification::assertSentTo(
            $this->customer,
            DownloadYourProductsNotification::class,
            function (DownloadYourProductsNotification $notification) use ($closure): bool {
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
            'has the customer name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->address->name, $emailContent);
            }],
            'has the order number' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString((string) $test->order->order_key, $emailContent);
            }],
            'has the download cta' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('Download Your Products!', $emailContent);
            }],
        ];
    }
}
