<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Product;

use App\Models\Shop\ShopProduct;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts(1, 1, 2);

        $this->product = ShopProduct::query()->first();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductDoesntExist(): void
    {
        $this->get(route('shop.product', ['product' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductDoesntHaveAnyLiveVariants(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->get(route('shop.product', ['product' => $product->slug]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itRendersTheShopProductPage(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->component('Shop/Product'));
    }

    #[Test]
    public function itReturnsTheProductInformation(): void
    {
        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has('product', fn (Assert $page) => $page->hasAll([
                        'id', 'title', 'description', 'long_description', 'image', 'additional_images', 'prices', 'rating', 'variants', 'category', 'variant_title', 'add-ons'
                    ]))
            );
    }

    #[Test]
    public function itReturnsTheTravelCardSpecificProductKeysIfTheProductIsInATravelCardCategory(): void
    {
        $this->product->categories->first()->update(['title' => 'Coeliac Gluten Free Travel Cards']);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has(
                        'product',
                        fn (Assert $page) => $page
                            ->where('is_travel_card', true)
                            ->has('countries')
                            ->etc()
                    )
            );

        $this->product->categories->first()->update(['title' => 'Coeliac+ Other Allergen Travel Cards']);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has(
                        'product',
                        fn (Assert $page) => $page
                            ->where('is_travel_card', true)
                            ->has('countries')
                            ->etc()
                    )
            );
    }

    #[Test]
    public function itDoesNotIncludeTheTravelCardSpecificProductKeysIfTheProductIsNotInATravelCardCategory(): void
    {
        $this->product->categories->first()->update(['title' => 'Foo Bar Baz']);

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has(
                        'product',
                        fn (Assert $page) => $page
                            ->missing('is_travel_card')
                            ->missing('countries')
                            ->etc()
                    )
            );
    }

    #[Test]
    public function itReturnsTheVariantsInTheProduct(): void
    {
        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has(
                        'product.variants',
                        2,
                    )
                    ->where('product.variants.0.title', 'Variant 0')
                    ->where('product.variants.1.title', 'Variant 1')
                    ->etc()
            );
    }

    public function makeRequest(): TestResponse
    {
        return $this->get(route('shop.product', ['product' => $this->product->slug]));
    }
}
