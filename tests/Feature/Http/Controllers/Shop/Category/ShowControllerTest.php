<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Category;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductPrice;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
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
            ->has($this->build(ShopProductPrice::class), 'prices')
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

    public function makeRequest(): TestResponse
    {
        return $this->get(route('shop.category', ['category' => $this->category->slug]));
    }
}
