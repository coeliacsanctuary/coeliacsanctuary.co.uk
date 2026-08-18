<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetRecentReviewsForShopIndexAction;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Resources\Shop\ShopIndexReviewResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetRecentReviewsForShopIndexActionTest extends TestCase
{
    protected string $cacheKey;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts(1, 3);

        $this->cacheKey = config('coeliac.cacheable.shop-reviews.index');
    }

    #[Test]
    public function itReturnsACollectionOfReviewResources(): void
    {
        $this->review($this->product(0), 5);

        $reviews = $this->callAction(GetRecentReviewsForShopIndexAction::class);

        $this->assertInstanceOf(AnonymousResourceCollection::class, $reviews);

        $reviews->each(function ($review): void {
            $this->assertInstanceOf(ShopIndexReviewResource::class, $review);
        });
    }

    #[Test]
    public function itReturnsNothingWhenThereAreNoReviews(): void
    {
        $this->assertCount(0, $this->callAction(GetRecentReviewsForShopIndexAction::class));
    }

    #[Test]
    public function itDoesntReturnReviewsRatedBelowFour(): void
    {
        $this->review($this->product(0), 3, 'Three stars');
        $this->review($this->product(1), 4, 'Four stars');
        $this->review($this->product(2), 5, 'Five stars');

        $this->assertEqualsCanonicalizing(['Four stars', 'Five stars'], $this->bodies());
    }

    #[Test]
    public function itDoesntReturnReviewsWithoutAnyText(): void
    {
        $this->review($this->product(0), 5, '');
        $this->review($this->product(1), 5, 'Has some words');

        $this->assertSame(['Has some words'], $this->bodies());
    }

    #[Test]
    public function itDoesntReturnReviewsForProductsThatArentLive(): void
    {
        $hidden = $this->product(0);
        $expected = $this->product(1);

        $this->review($hidden, 5, 'Retired product');
        $this->review($expected, 5, 'Live product');

        ShopProductVariant::query()->where('product_id', $hidden->id)->update(['live' => false]);

        $this->assertSame(['Live product'], $this->bodies());
    }

    #[Test]
    public function itReturnsTheNewestReviewsFirst(): void
    {
        $this->review($this->product(0), 5, 'Oldest', Carbon::now()->subDays(10));
        $this->review($this->product(1), 5, 'Newest', Carbon::now());
        $this->review($this->product(2), 5, 'Middle', Carbon::now()->subDays(5));

        $this->assertSame(['Newest', 'Middle', 'Oldest'], $this->bodies());
    }

    #[Test]
    public function itOnlyReturnsSixReviews(): void
    {
        foreach (range(1, 8) as $index) {
            $this->review($this->product(0), 5, "Review {$index}");
        }

        $this->assertCount(6, $this->callAction(GetRecentReviewsForShopIndexAction::class));
    }

    #[Test]
    public function itCachesTheReviews(): void
    {
        $this->review($this->product(0), 5);

        $this->assertFalse(Cache::has($this->cacheKey));

        $reviews = $this->callAction(GetRecentReviewsForShopIndexAction::class);

        $this->assertTrue(Cache::has($this->cacheKey));
        $this->assertSame($reviews, Cache::get($this->cacheKey));
    }

    protected function product(int $index): ShopProduct
    {
        return ShopProduct::query()->orderBy('id')->get()->get($index);
    }

    protected function review(ShopProduct $product, int $rating, string $body = 'A review', ?Carbon $createdAt = null): void
    {
        /** @var ShopOrderReview $review */
        $review = $this->create(ShopOrderReview::class);

        $this->build(ShopOrderReviewItem::class)
            ->forReview($review)
            ->forProduct($product)
            ->create([
                'rating' => $rating,
                'review' => $body,
                'created_at' => $createdAt ?? Carbon::now(),
            ]);
    }

    /** @return string[] */
    protected function bodies(): array
    {
        return $this->callAction(GetRecentReviewsForShopIndexAction::class)
            ->map(fn (ShopIndexReviewResource $review) => $review->review)
            ->values()
            ->toArray();
    }
}
