<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\DownloadMyProducts;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDownloadLink;
use App\Models\Shop\ShopOrderDownloadViews;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected ShopProduct $product;

    protected ShopProductAddOn $addOn;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('product-addons');

        $this->withCategoriesAndProducts(1, 1);

        $this->product = ShopProduct::query()->first();
        $this->addOn = $this->create(ShopProductAddOn::class, ['product_id' => $this->product->id]);
        $this->addOn->addMedia(UploadedFile::fake()->image('download.pdf'))->toMediaCollection('download');
    }

    #[Test]
    public function itReturnsNotFoundIfADownloadLinkDoesntExist(): void
    {
        $this->get(route('shop.download-my-products', 'foo'))->assertNotFound();
    }

    #[Test]
    public function itErrorsIfTheLinkIsExpired(): void
    {
        $downloadLink = $this->create(ShopOrderDownloadLink::class);

        TestTime::freeze();

        $url = URL::temporarySignedRoute('shop.download-my-products', now()->addSecond(), $downloadLink);

        TestTime::addSeconds(2);

        $this->get($url)->assertForbidden();
    }

    #[Test]
    public function itReturnsAFriendlyErrorPageIfTheSignedLinkHasExpired(): void
    {
        $downloadLink = $this->create(ShopOrderDownloadLink::class, [
            'expires_at' => now()->addSecond(),
        ]);

        TestTime::freeze();

        $url = URL::temporarySignedRoute('shop.download-my-products', $downloadLink->expires_at, $downloadLink);

        TestTime::addMinute();

        $this->get($url)->assertInertia(fn (Assert $page) => $page->component('Shop/DownloadMyProducts/Error'));
    }

    #[Test]
    public function itWorksOkForAValidLink(): void
    {
        $order = $this
            ->build(ShopOrder::class)
            ->asPaid()
            ->create();

        $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->add($this->product->variants->first())
            ->withAddOn($this->addOn)
            ->create();

        $downloadLink = $this
            ->build(ShopOrderDownloadLink::class)
            ->forOrder($order)
            ->create();

        $url = URL::temporarySignedRoute('shop.download-my-products', $downloadLink->expires_at, $downloadLink);

        $this->get($url)->assertOk();
    }

    #[Test]
    public function itReturnsTheExpectedData(): void
    {
        TestTime::freeze();

        $order = $this
            ->build(ShopOrder::class)
            ->asPaid()
            ->create();

        $orderItem = $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->add($this->product->variants->first())
            ->withAddOn($this->addOn)
            ->create();

        $downloadLink = $this
            ->build(ShopOrderDownloadLink::class)
            ->forOrder($order)
            ->create([
                'expires_at' => now()->addMonth(),
            ]);

        $url = URL::temporarySignedRoute('shop.download-my-products', $downloadLink->expires_at, $downloadLink);

        $this
            ->get($url)
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/DownloadMyProducts/DownloadMyProducts')
                    ->where('expires', $downloadLink->expires_at->format('jS F Y \a\t H:i'))
                    ->has(
                        'order',
                        fn (Assert $page) => $page
                            ->where('number', (string) $order->order_key)
                            ->where('name', $order->customer->name)
                            ->where('date', $order->payment->created_at->format('d/m/Y'))
                    )
                    ->has(
                        'items',
                        1,
                        fn (Assert $page) => $page
                            ->where('id', $orderItem->id)
                            ->where('title', $orderItem->product->title)
                            ->where('image', $orderItem->product->main_image_as_webp)
                            ->where('variant_title', $this->product->variants->first()->title)
                            ->where('variant_description', $this->product->variants->first()->short_description)
                            ->where('add_on_name', $this->addOn->name)
                            ->where('add_on_description', $this->addOn->description)
                            ->whereNotNull('download_link')
                    )
            );
    }

    #[Test]
    public function itLogsAViewAgainstTheDownloadLinkRecord(): void
    {
        $this->assertDatabaseEmpty(ShopOrderDownloadViews::class);

        $order = $this
            ->build(ShopOrder::class)
            ->asPaid()
            ->create();

        $this->build(ShopOrderItem::class)
            ->inOrder($order)
            ->add($this->product->variants->first())
            ->withAddOn($this->addOn)
            ->create();

        $downloadLink = $this
            ->build(ShopOrderDownloadLink::class)
            ->forOrder($order)
            ->create();

        $url = URL::temporarySignedRoute('shop.download-my-products', $downloadLink->expires_at, $downloadLink);

        $this->get($url)->assertOk();

        $this->assertDatabaseCount(ShopOrderDownloadViews::class, 1);
        $this->assertNotEmpty($downloadLink->refresh()->views);
    }
}
