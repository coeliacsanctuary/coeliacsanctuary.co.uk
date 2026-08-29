<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopFeedback;
use App\Models\Shop\ShopOrderReview;
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

    #[Test]
    public function googleMerchantEnabledDefaultsToTrue(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->assertTrue($product->google_merchant_enabled);
    }

    #[Test]
    public function googleMerchantEnabledIsCastToBoolean(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->assertIsBool($product->google_merchant_enabled);
    }

    #[Test]
    public function googleMerchantProductIdDefaultsToNull(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->assertNull($product->google_merchant_product_id);
    }

    #[Test]
    public function theFactoryCanCreateAGoogleMerchantDisabledProduct(): void
    {
        $product = $this->build(ShopProduct::class)->googleMerchantDisabled()->create();

        $this->assertFalse($product->google_merchant_enabled);
    }

    #[Test]
    public function itOnlyReturnsOneReviewPerCustomerReviewPerProduct(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->buildReviewWithItems($product, ['The real review', 'Same as above', 'Same as above']);

        $this->assertDatabaseCount(ShopOrderReviewItem::class, 3);
        $this->assertCount(1, $product->reviews);
    }

    #[Test]
    public function itPrefersTheReviewItemThatHasReviewText(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->buildReviewWithItems($product, [null, 'The real review']);

        $this->assertSame('The real review', $product->reviews->first()->review);
    }

    #[Test]
    public function itDoesNotDeduplicateOneReviewAcrossDifferentProducts(): void
    {
        $productOne = $this->create(ShopProduct::class);
        $productTwo = $this->create(ShopProduct::class);

        $review = $this->build(ShopOrderReview::class)->create();

        foreach ([$productOne, $productTwo] as $product) {
            $this->build(ShopOrderReviewItem::class)
                ->forReview($review)
                ->forProduct($product)
                ->create(['review' => 'Bought both, both great']);
        }

        $this->assertCount(1, $productOne->reviews);
        $this->assertCount(1, $productTwo->reviews);
    }

    #[Test]
    public function itSchemasReviewsUnderTheReviewKeyRatherThanTheSupersededReviewsKey(): void
    {
        $product = $this->schemaReadyProduct();

        $this->buildReviewWithItems($product, ['A genuinely useful review']);

        $schema = $this->schemaFor($product);

        $this->assertArrayHasKey('review', $schema);
        $this->assertArrayNotHasKey('reviews', $schema);
    }

    #[Test]
    public function itSchemasTheReviewBodyAndPublishedDate(): void
    {
        $product = $this->schemaReadyProduct();

        $this->buildReviewWithItems($product, ['A genuinely useful review']);

        $review = $this->schemaFor($product)['review'][0];

        $this->assertSame('A genuinely useful review', $review['reviewBody']);
        $this->assertArrayHasKey('datePublished', $review);
    }

    #[Test]
    public function itDoesNotSchemaReviewItemsWithNoReviewText(): void
    {
        $product = $this->schemaReadyProduct();

        $this->buildReviewWithItems($product, ['A genuinely useful review']);
        $this->buildReviewWithItems($product, [null]);

        $this->assertCount(1, $this->schemaFor($product)['review']);
        $this->assertSame(2, $this->schemaFor($product)['aggregateRating']['reviewCount']);
    }

    #[Test]
    public function itCapsTheNumberOfSchemadReviews(): void
    {
        $product = $this->schemaReadyProduct();

        foreach (range(1, 25) as $index) {
            $this->buildReviewWithItems($product, ["Review number {$index}"]);
        }

        $this->assertCount(20, $this->schemaFor($product)['review']);
        $this->assertSame(25, $this->schemaFor($product)['aggregateRating']['reviewCount']);
    }

    #[Test]
    public function itSchemasTheAggregateRatingRoundedToTwoDecimalPlaces(): void
    {
        $product = $this->schemaReadyProduct();

        foreach ([5, 4, 4] as $rating) {
            $this->build(ShopOrderReviewItem::class)
                ->forReview($this->build(ShopOrderReview::class)->create())
                ->forProduct($product)
                ->create(['review' => 'Review', 'rating' => $rating]);
        }

        $this->assertSame(4.33, $this->schemaFor($product)['aggregateRating']['ratingValue']);
    }

    #[Test]
    public function itSchemasTheShippingDestinationAsAnIsoCountryCode(): void
    {
        $product = $this->schemaReadyProduct();

        $shippingDetails = $this->schemaFor($product)['offers']['shippingDetails'];

        $this->assertSame('GB', $shippingDetails['shippingDestination']['addressCountry']);
    }

    #[Test]
    public function itSchemasTheCutoffTimeAsATimeWithoutADate(): void
    {
        $product = $this->schemaReadyProduct();

        $cutoffTime = $this->schemaFor($product)['offers']['shippingDetails']['deliveryTime']['cutoffTime'];

        $this->assertSame('14:00:00Z', $cutoffTime);
    }

    #[Test]
    public function itSchemasTheMerchantReturnPolicy(): void
    {
        $product = $this->schemaReadyProduct();

        $policy = $this->schemaFor($product)['offers']['hasMerchantReturnPolicy'];

        $this->assertSame('GB', $policy['applicableCountry']);
        $this->assertSame(14, $policy['merchantReturnDays']);
        $this->assertSame('https://schema.org/MerchantReturnFiniteReturnWindow', $policy['returnPolicyCategory']);
        $this->assertSame('https://schema.org/ReturnByMail', $policy['returnMethod']);
    }

    #[Test]
    public function itCanSchemaAProductWithNoRelationsLoaded(): void
    {
        $product = $this->schemaReadyProduct();

        $this->buildReviewWithItems($product, ['A genuinely useful review']);

        $schema = $this->schemaFor(ShopProduct::query()->findOrFail($product->id));

        $this->assertCount(1, $schema['review']);
        $this->assertArrayHasKey('shippingRate', $schema['offers']['shippingDetails']);
    }

    /** schema() reads the primary image and the variants, and the model's live scope needs a variant to exist. */
    protected function schemaReadyProduct(): ShopProduct
    {
        Storage::fake('media');

        $product = $this->create(ShopProduct::class);

        $product->addMedia(UploadedFile::fake()->image('primary.jpg'))->toMediaCollection('primary');

        $this->build(ShopProductVariant::class)->belongsToProduct($product)->create();

        $this->build(ShopPrice::class)->forProduct($product)->create();

        return $product->refresh();
    }

    /** @return array<string, mixed> */
    protected function schemaFor(ShopProduct $product): array
    {
        /** @var array<string, mixed> $schema */
        $schema = json_decode(strip_tags($product->schema()->toScript()), true);

        return $schema;
    }

    /** @param array<int, string|null> $reviews */
    protected function buildReviewWithItems(ShopProduct $product, array $reviews): void
    {
        $review = $this->build(ShopOrderReview::class)->create();

        foreach ($reviews as $body) {
            $this->build(ShopOrderReviewItem::class)
                ->forReview($review)
                ->forProduct($product)
                ->create(['review' => $body, 'rating' => 5]);
        }
    }
}
