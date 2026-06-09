<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use App\Mailables\Shop\DownloadYourProductsMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;
use Database\Seeders\ShopScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DownloadYourProductsMailableTest extends TestCase
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

        $this->order = $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create();

        $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->add($variant)
            ->withAddOn($this->build(ShopProductAddOn::class)->forProduct($product)->create())
            ->create();

        $this->downloadLink = $this->create(ShopOrderDownloadLink::class, [
            'order_id' => $this->order->id,
        ]);
    }

    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            DownloadYourProductsMailable::make($this->downloadLink, 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = DownloadYourProductsMailable::make($this->downloadLink, 'foo');

        $this->assertEquals('Your Coeliac Sanctuary digital downloads are ready!', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = DownloadYourProductsMailable::make($this->downloadLink, 'foo');

        $this->assertEquals('mailables.mjml.shop.download-your-products', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        $data = [
            'downloadLink' => fn ($assertionDownloadLink) => $this->assertNotNull($assertionDownloadLink),
            'order' => fn ($assertionOrder) => $this->assertTrue($this->order->is($assertionOrder)),
            'notifiable' => fn ($customer) => $this->assertTrue($this->customer->is($customer)),
            'reason' => fn ($reason) => $this->assertEquals('so you can download your digital products purchased through Coeliac Sanctuary Shop.', $reason),
        ];

        $mailable = DownloadYourProductsMailable::make($this->downloadLink, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
