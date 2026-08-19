<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Category;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected ShopCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts(1, 5);

        $this->category = ShopCategory::query()->first();
    }

    #[Test]
    public function itReturnsNotFoundIfTheCategoryDoesntExist(): void
    {
        $this->get(route('shop.category', ['category' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheCategoryDoesntHaveAnyLiveProducts(): void
    {
        $category = $this->create(ShopCategory::class);

        $this->get(route('shop.category', ['category' => $category->slug]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRendersTheShopCategoryPage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/Category'));
    }

    #[Test]
    public function itReturnsTheCategoryInformation(): void
    {
        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Category')
                    ->has('category', fn (Assert $page) => $page->hasAll(['title', 'description', 'image', 'link', 'travelCardSearch']))
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsTheProductsInTheCategory(): void
    {
        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Category')
                    ->has(
                        'products',
                        5,
                    )
                    ->where('products.0.title', 'Product 0')
                    ->where('products.1.title', 'Product 1')
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsThePinnedProductFirst(): void
    {
        $this->build(ShopProduct::class)
            ->inCategory($this->category)
            ->has($this->build(ShopProductVariant::class), 'variants')
            ->has($this->build(ShopPrice::class), 'prices')
            ->pinned()
            ->state(fn () => ['title' => 'This is a Pinned Product'])
            ->afterCreating(function (ShopProduct $product): void {
                $product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('primary');
                $product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('social');
            })
            ->create();

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Category')
                    ->has('products')
                    ->where('products.0.title', 'This is a Pinned Product')
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntListAProductThatIsntLive(): void
    {
        $this->build(ShopProduct::class)
            ->inCategory($this->category)
            ->state(fn () => ['title' => 'This is a Not Live Product'])
            ->create();

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Category')
                    ->has(
                        'products',
                        fn (Assert $page) => $page
                            ->each(fn (Assert $page) => $page
                                ->whereNot('title', 'This is a Not Live Product')
                                ->etc())
                    )
            );
    }

    #[Test]
    public function itIncludesTheProductListSchema(): void
    {
        $this->makeRequest()->assertInertia(function (Assert $page): void {
            /** @var string[] $schema */
            $schema = $page->toArray()['props']['meta']['schema'];

            $itemList = collect($schema)->first(fn (string $item) => str_contains($item, 'ItemList'));

            $this->assertNotNull($itemList);
            $this->assertStringContainsString('Product 0', $itemList);
        });
    }

    #[Test]
    public function itReturnsThatAProductWithStockIsInStock(): void
    {
        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has(
                        'products',
                        fn (Assert $page) => $page
                            ->each(fn (Assert $page) => $page->where('in_stock', true)->etc())
                    )
            );
    }

    #[Test]
    public function itReturnsThatAProductWithNoStockIsNotInStock(): void
    {
        $this->buildProductInCategory('This Product Is Out Of Stock', variantState: fn () => ['quantity' => 0]);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('products', 6)
                    ->where('products.5.title', 'This Product Is Out Of Stock')
                    ->where('products.5.in_stock', false)
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsAFootnoteListingEveryCountryForATravelCardProduct(): void
    {
        $product = $this->buildProductInCategory('A Travel Card Product');

        $this->attachCountry($product, 'france', 10);
        $this->attachCountry($product, 'belgium', 5);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('products.0.title', 'A Travel Card Product')
                    ->where('products.0.footnote', 'Covers France and Belgium')
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsAFootnoteWithACountForATravelCardProductWithMoreThanThreeCountries(): void
    {
        $product = $this->buildProductInCategory('A Travel Card Product');

        $this->attachCountry($product, 'france', 10);
        $this->attachCountry($product, 'belgium', 9);
        $this->attachCountry($product, 'germany', 8);
        $this->attachCountry($product, 'austria', 7);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('products.0.title', 'A Travel Card Product')
                    ->where('products.0.footnote', 'Covers 4 countries including France, Belgium and Germany')
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntIncludeCountriesInTheFootnoteThatArentFlaggedToShow(): void
    {
        $product = $this->buildProductInCategory('A Travel Card Product');

        $this->attachCountry($product, 'france', 10, showOnProductPage: false);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('products.0.title', 'A Travel Card Product')
                    ->where('products.0.footnote', null)
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntReturnAFootnoteForAProductInANonTravelCardCategory(): void
    {
        $category = $this->create(ShopCategory::class, ['id' => 5]);
        $category->addMedia(UploadedFile::fake()->image('category.jpg'))->toMediaCollection('primary');
        $category->addMedia(UploadedFile::fake()->image('category.jpg'))->toMediaCollection('social');

        $this->category = $category;

        $product = $this->buildProductInCategory('A Non Travel Card Product');

        $this->attachCountry($product, 'france', 10);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('products.0.title', 'A Non Travel Card Product')
                    ->where('products.0.footnote', null)
                    ->etc()
            );
    }

    protected function attachCountry(ShopProduct $product, string $term, int $score, bool $showOnProductPage = true): void
    {
        $product->travelCardSearchTerms()->attach(
            $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => 'country']),
            ['card_show_on_product_page' => $showOnProductPage, 'card_score' => $score, 'card_language' => 'french'],
        );
    }

    #[Test]
    public function itDoesntListAProductThatHasNoCurrentPrice(): void
    {
        $this->buildProductInCategory('This Product Has An Expired Price', priceState: fn () => ['end_at' => Carbon::now()->subDay()]);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('products', 5)
                    ->has(
                        'products',
                        fn (Assert $page) => $page
                            ->each(fn (Assert $page) => $page
                                ->whereNot('title', 'This Product Has An Expired Price')
                                ->etc())
                    )
            );
    }

    protected function buildProductInCategory(string $title, ?callable $variantState = null, ?callable $priceState = null): ShopProduct
    {
        $variant = $this->build(ShopProductVariant::class);
        $price = $this->build(ShopPrice::class);

        return $this->build(ShopProduct::class)
            ->inCategory($this->category)
            ->has($variantState ? $variant->state($variantState) : $variant, 'variants')
            ->has($priceState ? $price->state($priceState) : $price, 'prices')
            ->state(fn () => ['title' => $title])
            ->afterCreating(function (ShopProduct $product): void {
                $product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('primary');
                $product->addMedia(UploadedFile::fake()->image('product.jpg'))->toMediaCollection('social');
            })
            ->create();
    }

    public function makeRequest(): TestResponse
    {
        return $this->get(route('shop.category', ['category' => $this->category->slug]));
    }
}
