<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\DigitalCards;

use App\Actions\Shop\DigitalCards\GetDigitalCardsIndexAction;
use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected ShopCategory $category;

    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var ShopProduct $product */
        $product = $this->build(ShopProduct::class)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->create();

        $this->product = $product;

        /** @var ShopCategory $category */
        $category = $this->create(ShopCategory::class, ['id' => DigitalCardType::STANDARD->categoryId()]);

        $category->products()->attach($this->product);

        $this->category = $category;
    }

    /** @return Collection<int, ShopDigitalCard> */
    protected function cards(array $attributes = []): Collection
    {
        /** @var ShopDigitalCard $card */
        $card = $this->build(ShopDigitalCard::class)
            ->forProduct($this->product)
            ->has($this->build(ShopDigitalCardPrice::class)->state(fn () => ['price' => 500]), 'price')
            ->state($attributes)
            ->create();

        return collect([$card->load('price')]);
    }

    protected function makeRequest(): TestResponse
    {
        return $this->get(route('shop.digital-standard-cards'));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->expectAction(GetDigitalCardsIndexAction::class, return: collect());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRendersTheDigitalCardsPage(): void
    {
        $this->expectAction(GetDigitalCardsIndexAction::class, return: collect());

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/DigitalCards'));
    }

    #[Test]
    public function itSetsThePageTitleToTheCardType(): void
    {
        $this->expectAction(GetDigitalCardsIndexAction::class, return: collect());

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page->where('meta.title', 'Digital Coeliac Gluten Free Travel Cards')->etc()
        );
    }

    #[Test]
    public function itReturnsTheCardTypeAndALinkBackToTheCategory(): void
    {
        $this->expectAction(GetDigitalCardsIndexAction::class, return: collect());

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('type', 'Digital Coeliac Gluten Free Travel Cards')
                ->where('categoryLink', route('shop.category', $this->category))
                ->etc()
        );
    }

    #[Test]
    public function itCallsTheGetDigitalCardsIndexActionWithTheTypeResolvedFromTheRequestPath(): void
    {
        $this->expectAction(GetDigitalCardsIndexAction::class, [DigitalCardType::STANDARD], return: collect());

        $this->makeRequest();
    }

    #[Test]
    public function itReturnsTheCardsFromTheAction(): void
    {
        $this->expectAction(
            GetDigitalCardsIndexAction::class,
            return: $this->cards(['title' => 'French Travel Card', 'language' => 'french', 'native_language' => 'français']),
        );

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->has('products', 1)
                ->where('products.0.title', 'French Travel Card')
                ->where('products.0.language', 'french')
                ->where('products.0.native_language', 'français')
                ->where('products.0.price', '£5.00')
                ->etc()
        );
    }

    #[Test]
    public function itReturnsNoProductsWhenTheActionFindsNoCards(): void
    {
        $this->expectAction(GetDigitalCardsIndexAction::class, return: collect());

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->has('products', 0)->etc());
    }

    #[Test]
    public function itReturnsNotFoundWhenTheCategoryHasNoLiveProducts(): void
    {
        $this->category->products()->detach();

        $this->makeRequest()->assertNotFound();
    }
}
