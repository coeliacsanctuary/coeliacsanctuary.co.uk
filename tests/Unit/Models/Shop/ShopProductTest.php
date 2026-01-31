<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopFeedback;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\ShopShippingMethod;
use App\Models\Shop\TravelCardSearchTerm;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopProductTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
    }

    #[Test]
    public function itHasALiveScope(): void
    {
        $this->assertNotEmpty(ShopProduct::query()->toBase()->wheres);
    }

    #[Test]
    public function itCanHaveMedia(): void
    {
        Storage::fake('media');

        $product = $this->create(ShopProduct::class);

        $product->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');
        $product->addMedia(UploadedFile::fake()->image('primary.jpg'))->toMediaCollection('primary');

        $this->assertCount(2, $product->media);
    }

    #[Test]
    public function itCanGenerateALink(): void
    {
        $product = $this->create(ShopProduct::class, [
            'slug' => 'test-product',
        ]);

        $this->assertEquals('/shop/product/test-product', $product->link);
    }

    #[Test]
    public function itHasManyCategories(): void
    {
        ShopCategory::withoutGlobalScopes();
        ShopProduct::withoutGlobalScopes();
        ShopProductVariant::withoutGlobalScopes();

        $categories = $this->build(ShopCategory::class)
            ->count(5)
            ->create();

        $product = $this->create(ShopProduct::class);

        $product->categories()->attach($categories->pluck('id')->toArray());

        $this->assertCount(5, $product->categories()->withoutGlobalScopes()->get());
    }

    #[Test]
    public function itHasAShippingMethod(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->assertInstanceOf(ShopShippingMethod::class, $product->shippingMethod);
    }

    #[Test]
    public function itHasManyVariants(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopProductVariant::class)
            ->count(5)
            ->belongsToProduct($product)
            ->create();

        $this->assertInstanceOf(Collection::class, $product->refresh()->variants);
    }

    #[Test]
    public function itHasManyPrices(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->count(5)
            ->forProduct($product)
            ->create();

        $this->assertInstanceOf(Collection::class, $product->refresh()->prices);
    }

    #[Test]
    public function itHasFeedback(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopFeedback::class)
            ->count(5)
            ->forProduct($product)
            ->create();

        $this->assertInstanceOf(Collection::class, $product->refresh()->feedback);
    }

    #[Test]
    public function itHasReviews(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)
            ->count(5)
            ->forProduct($product)
            ->create();

        $this->assertInstanceOf(Collection::class, $product->refresh()->reviews);
    }

    #[Test]
    public function itHasSearchTerms(): void
    {
        $product = $this->create(ShopProduct::class);

        $searchTerms = $this->build(TravelCardSearchTerm::class)
            ->count(5)
            ->create();

        $product->travelCardSearchTerms()->attach($searchTerms);

        $this->assertInstanceOf(Collection::class, $product->refresh()->travelCardSearchTerms);
    }

    #[Test]
    public function itCanGetACollectionOfCurrentPrices(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->ended()
            ->create();

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create();

        $this->assertCount(1, $product->currentPrices());
    }

    #[Test]
    public function itCanGetTheCurrentPrice(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->ended()
            ->create([
                'price' => 200,
            ]);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 100,
            ]);

        $this->assertEquals(100, $product->currentPrice);
    }

    #[Test]
    public function itReturnsTheOldPrice(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->onSale()
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 200,
            ]);

        $this->assertEquals(200, $product->oldPrice);
    }

    #[Test]
    public function itReturnsTheOldPriceAsNullIfNotOnSale(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 200,
            ]);

        $this->assertNull($product->oldPrice);
    }

    #[Test]
    public function itReturnsAPriceObject(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->onSale()
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 200,
            ]);

        $this->assertEquals(['current_price' => '£1.00', 'old_price' => '£2.00', 'raw_price' => 100], $product->price);
    }

    #[Test]
    public function itReturnsAPriceObjectWithoutAnOldPrice(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 100,
            ]);

        $this->build(ShopPrice::class)
            ->forProduct($product)
            ->create([
                'price' => 200,
            ]);

        $this->assertEquals(['current_price' => '£1.00', 'raw_price' => 100], $product->price);
    }
}
