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
use Spatie\TestTime\TestTime;
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
    public function itReturnsThePrimaryVariant(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->create();

        $primaryVariant = $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->isPrimary()
            ->create();

        $this->assertFalse($primaryVariant->is($product->variants->first()));

        $this->assertTrue($primaryVariant->is($product->primaryVariant()));
    }

    #[Test]
    public function itReturnsTheFirstVariantAsThePrimaryVariantIfNoneAreSetAsPrimary(): void
    {
        $product = $this->create(ShopProduct::class);

        $firstVariant = $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->create();

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->create();

        $this->assertTrue($firstVariant->is($product->variants->first()));

        $this->assertTrue($firstVariant->is($product->primaryVariant()));
    }

    #[Test]
    public function itGetsThePrimaryVariantPriceAsTheFromPrice(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->isPrimary()
            ->has($this->build(ShopPrice::class)->state(['price' => 200]), 'prices')
            ->create();

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->has($this->build(ShopPrice::class)->state(['price' => 100]), 'prices')
            ->create();

        $this->assertEquals(200, $product->from_price);
    }

    #[Test]
    public function itGetsTheLowestVariantPriceAsTheFromPriceIfNoPrimaryVariantIsSet(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->has($this->build(ShopPrice::class)->state(['price' => 200]), 'prices')
            ->create();

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->has($this->build(ShopPrice::class)->state(['price' => 100]), 'prices')
            ->create();

        $this->assertEquals(100, $product->from_price);
    }

    #[Test]
    public function itReturnsFalseForAProductHavingMultiplePricesIfAPrimaryVariantExists(): void
    {
        TestTime::setTestNow('2025-01-01');

        $product = $this->create(ShopProduct::class);

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->isPrimary()
            ->has($this->build(ShopPrice::class)->state(['price' => 200]), 'prices')
            ->create();

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->has($this->build(ShopPrice::class)->state(['price' => 100]), 'prices')
            ->create();

        $this->assertFalse($product->hasMultiplePrices());
    }

    #[Test]
    public function itDeterminesWhetherAProductHasMultiplePricesIfNoPrimaryIsSet(): void
    {
        TestTime::setTestNow('2025-01-01');

        $product = $this->create(ShopProduct::class);

        $variant = $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->has($this->build(ShopPrice::class)->state(['price' => 200]), 'prices')
            ->create();

        $this->build(ShopProductVariant::class)
            ->belongsToProduct($product)
            ->has($this->build(ShopPrice::class)->state(['price' => 100]), 'prices')
            ->create();

        $this->assertTrue($product->hasMultiplePrices());

        TestTime::addDays(1);

        $variant->prices()->first()->update(['price' => 100]);

        $this->assertFalse($product->refresh()->hasMultiplePrices());

        TestTime::addDays(1);

        $variant->prices()->first()->update(['price' => 200]);

        $this->assertTrue($product->refresh()->hasMultiplePrices());

        TestTime::addDays(1);

        $this->build(ShopPrice::class)->forVariant($variant)->create([
            'price' => '100',
            'sale_price' => true,
        ]);

        $this->assertFalse($product->refresh()->hasMultiplePrices());
    }
}
