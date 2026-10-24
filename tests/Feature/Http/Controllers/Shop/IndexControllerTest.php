<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopDigitalCardType;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Money\Money;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected ShopDigitalCardType $cardType;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts();

        /** @var ShopDigitalCardType $cardType */
        $cardType = ShopDigitalCardType::query()->findOrFail(DigitalCardType::STANDARD->value);

        $cardType->addMedia(UploadedFile::fake()->image('digital-card.jpg'))->toMediaCollection('primary');
        $cardType->addMedia(UploadedFile::fake()->image('digital-card.jpg'))->toMediaCollection('social');

        $this->cardType = $cardType;
    }

    #[Test]
    public function itLoadsTheShopIndexPage(): void
    {
        $this->get(route('shop.index'))->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['shop']);

        $this->get(route('shop.index'));
    }

    #[Test]
    public function itReturnsTheTravelCardCategories(): void
    {
        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Index')
                    ->has(
                        'categories.travelCards',
                        1,
                        fn (Assert $page) => $page->hasAll([
                            'title', 'description', 'image', 'link', 'travelCardSearch',
                            'products_count', 'price', 'reviews_count',
                        ])
                    )
                    ->where('categories.travelCards.0.title', 'Category 0')
                    ->where('categories.travelCards.0.travelCardSearch', true)
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsEveryOtherCategory(): void
    {
        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Index')
                    ->has(
                        'categories.everythingElse',
                        4,
                        fn (Assert $page) => $page->hasAll([
                            'title', 'description', 'image', 'link', 'travelCardSearch',
                            'products_count', 'price', 'reviews_count',
                        ])
                    )
                    ->where('categories.everythingElse.0.title', 'Category 1')
                    ->where('categories.everythingElse.1.title', 'Category 2')
                    ->where('categories.everythingElse.0.travelCardSearch', false)
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsTheLiveDigitalCardTypes(): void
    {
        config(['coeliac.digital_cards_enabled' => true]);

        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'digitalCards',
                        1,
                        fn (Assert $page) => $page->hasAll(['id', 'pageTitle', 'link', 'image'])
                    )
                    ->where('digitalCards.0.id', DigitalCardType::STANDARD->value)
                    ->where('digitalCards.0.pageTitle', $this->cardType->title)
                    ->where('digitalCards.0.link', route('shop.digital-standard-cards'))
                    ->where('digitalCards.0.image', $this->cardType->main_image_as_webp ?? $this->cardType->main_image)
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntReturnADigitalCardTypeThatIsntLive(): void
    {
        config(['coeliac.digital_cards_enabled' => true]);

        $this->cardType->update(['live' => false]);

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page->has('digitalCards', 0)->etc());
    }

    #[Test]
    public function itDoesntReturnAnyDigitalCardTypesWhenDigitalCardsAreDisabled(): void
    {
        config(['coeliac.digital_cards_enabled' => false]);

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page->has('digitalCards', 0)->etc());
    }

    #[Test]
    public function itReturnsTheProductCountPriceAndReviewCountForEachCategory(): void
    {
        /** @var ShopCategory $category */
        $category = ShopCategory::query()->first();

        /** @var ShopProduct $product */
        $product = $category->products()->first();

        /** @var ShopOrderReview $review */
        $review = $this->create(ShopOrderReview::class);

        $this->build(ShopOrderReviewItem::class)
            ->forReview($review)
            ->forProduct($product)
            ->create(['rating' => 5, 'review' => 'Great']);

        $cheapest = $category->products()->with('prices')->get()->min(fn (ShopProduct $product) => $product->currentPrice);

        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('categories.travelCards.0.products_count', 2)
                    ->where('categories.travelCards.0.reviews_count', 1)
                    ->where('categories.travelCards.0.price', 'from ' . Helpers::formatMoney(Money::GBP($cheapest)))
                    ->etc()
            );
    }

    #[Test]
    public function itAlwaysQuotesTheCategoryPriceAsAFromPrice(): void
    {
        ShopPrice::query()->update(['price' => 250]);

        $this->get(route('shop.index'))
            ->assertInertia(fn (Assert $page) => $page->where('categories.travelCards.0.price', 'from £2.50')->etc());
    }

    #[Test]
    public function itReturnsTheBestSellingProducts(): void
    {
        /** @var ShopProduct $product */
        $product = ShopProduct::query()->first();

        /** @var ShopOrder $order */
        $order = $this->build(ShopOrder::class)->asPaid()->create();

        $this->build(ShopOrderItem::class)->inOrder($order)->inProduct($product)->create(['quantity' => 5]);

        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'popularProducts',
                        1,
                        fn (Assert $page) => $page->hasAll(['title', 'link', 'image', 'price'])->etc()
                    )
                    ->where('popularProducts.0.title', $product->title)
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsTheRecentReviews(): void
    {
        /** @var ShopOrderReview $review */
        $review = $this->create(ShopOrderReview::class, ['name' => 'Jane Doe']);

        $this->build(ShopOrderReviewItem::class)
            ->forReview($review)
            ->forProduct(ShopProduct::query()->first())
            ->create(['rating' => 5, 'review' => 'Brilliant cards']);

        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'reviews',
                        1,
                        fn (Assert $page) => $page->hasAll(['name', 'review', 'rating', 'product'])
                    )
                    ->where('reviews.0.name', 'Jane Doe')
                    ->where('reviews.0.review', 'Brilliant cards')
                    ->etc()
            );
    }

    #[Test]
    public function itIncludesTheCategoryListSchema(): void
    {
        $this->get(route('shop.index'))->assertInertia(function (Assert $page): void {
            /** @var string[] $schema */
            $schema = $page->toArray()['props']['meta']['schema'];

            $itemList = collect($schema)->first(fn (string $item) => str_contains($item, 'ItemList'));

            $this->assertNotNull($itemList);
            $this->assertStringContainsString('Category 0', $itemList);
        });
    }

    #[Test]
    public function itDoesntReturnACategoryThatDoesntHaveAnyLiveProducts(): void
    {
        $this->create(ShopCategory::class, [
            'title' => 'No Products',
        ]);

        $this->get(route('shop.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Index')
                    ->has(
                        'categories.everythingElse',
                        fn (Assert $page) => $page
                            ->each(fn (Assert $page) => $page
                                ->whereNot('title', 'No Products')
                                ->etc())
                    )
                    ->etc()
            );
    }
}
