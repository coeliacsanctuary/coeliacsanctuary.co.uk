<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Product;

use App\Models\Faqs\Faq;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
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

        // The seeder creates category 1, which is a travel card category. Tests that want the travel
        // card resource opt in explicitly, so everything else runs against the standard resource.
        $this->assignProductToCategory(99);
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
    public function itLinksToTheRealCategoryUrlInTheBreadcrumbSchema(): void
    {
        /** @var ShopCategory $category */
        $category = $this->product->categories()->first();

        $this->makeRequest()->assertInertia(function (Assert $page) use ($category): void {
            /** @var string[] $schema */
            $schema = $page->toArray()['props']['meta']['schema'];

            $breadcrumbs = collect($schema)->first(fn (string $item) => str_contains($item, 'BreadcrumbList'));

            $this->assertNotNull($breadcrumbs);
            $this->assertStringContainsString(route('shop.category', $category), $breadcrumbs);
            $this->assertStringNotContainsString('\/shop\/slug', $breadcrumbs);
        });
    }

    #[Test]
    public function itReturnsTheProductInformation(): void
    {
        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has('product', fn (Assert $page) => $page->hasAll([
                        'id', 'title', 'description', 'long_description', 'image', 'additional_images', 'prices', 'rating', 'variants', 'category', 'variant_title', 'add_ons', 'faqs'
                    ]))
            );
    }

    #[Test]
    #[DataProvider('travelCardCategoryIdsDataProvider')]
    public function itReturnsTheTravelCardSpecificProductKeysIfTheProductIsInATravelCardCategory(int $categoryId): void
    {
        $this->assignProductToCategory($categoryId);

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

    /** @return array<string, array{int}> */
    public static function travelCardCategoryIdsDataProvider(): array
    {
        return [
            'standard travel cards' => [1],
            'coeliac plus travel cards' => [11],
        ];
    }

    #[Test]
    public function itDoesNotIncludeTheTravelCardSpecificProductKeysIfTheProductIsNotInATravelCardCategory(): void
    {
        $this->assignProductToCategory(99);

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
    public function itDoesNotUseTheTravelCardResourceForACategoryMerelyTitledLikeOne(): void
    {
        $this->assignProductToCategory(99, 'Coeliac Gluten Free Travel Cards');

        $this->makeRequest()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Shop/Product')
                    ->has('product', fn (Assert $page) => $page->missing('is_travel_card')->etc())
            );
    }

    #[Test]
    public function itReturnsNotFoundWhenTheProductHasNoCurrentPrice(): void
    {
        $this->product->prices()->delete();

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itOnlyReturnsOneReviewPerReviewPerProduct(): void
    {
        $this->buildReviewWithItems(['The real review', 'Same as above', null]);

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->has('reviews.data', 1));
    }

    #[Test]
    public function itKeepsTheReviewItemThatActuallyHasReviewTextWhenDeduplicating(): void
    {
        $this->buildReviewWithItems([null, 'The real review', 'Same as above']);

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page->where('reviews.data.0.review', 'The real review')
        );
    }

    #[Test]
    public function itCountsDeduplicatedReviewsInTheProductRating(): void
    {
        $this->buildReviewWithItems(['The real review', 'Same as above', 'Same as above']);

        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->where('product.rating.count', 1)->etc());
    }

    #[Test]
    public function itReturnsTheFaqsForTheProduct(): void
    {
        $this->build(Faq::class)->on($this->product)->create(['question' => 'Is this gluten free?', 'answer' => 'Yes!']);

        $this->makeRequest()->assertInertia(
            fn (Assert $page) => $page
                ->where('product.faqs.0.question', 'Is this gluten free?')
                ->where('product.faqs.0.answer', 'Yes!')
                ->etc()
        );
    }

    #[Test]
    public function itReturnsNullFaqsWhenTheProductHasNone(): void
    {
        $this->makeRequest()->assertInertia(fn (Assert $page) => $page->where('product.faqs', null)->etc());
    }

    #[Test]
    public function itDoesntSchemaTheFaqsWhenTheProductHasNone(): void
    {
        $this->assertStringNotContainsString('FAQPage', implode('', $this->schemaFor($this->makeRequest())));
    }

    #[Test]
    public function itSchemasTheFaqsWhenTheProductHasSome(): void
    {
        $this->build(Faq::class)->on($this->product)->create(['question' => 'Is this gluten free?', 'answer' => 'Yes!']);

        $schema = implode('', $this->schemaFor($this->makeRequest()));

        $this->assertStringContainsString('"@type":"FAQPage"', $schema);
        $this->assertStringContainsString('"name":"Is this gluten free?"', $schema);
        $this->assertStringContainsString('"text":"Yes!"', $schema);
    }

    protected function assignProductToCategory(int $categoryId, string $title = 'Some Category'): void
    {
        // ShopCategory is behind a LiveScope that requires it to still have products, which won't
        // hold for whichever category the product is being moved away from.
        $category = ShopCategory::query()->withoutGlobalScopes()->find($categoryId) ?? $this->build(ShopCategory::class)->create([
            'id' => $categoryId,
            'title' => $title,
        ]);

        $this->product->categories()->sync([$category->id]);

        $this->product->unsetRelation('categories');
    }

    /** @param array<int, string|null> $reviews */
    protected function buildReviewWithItems(array $reviews): void
    {
        $review = $this->build(ShopOrderReview::class)->create();

        foreach ($reviews as $body) {
            $this->build(ShopOrderReviewItem::class)
                ->forReview($review)
                ->forProduct($this->product)
                ->create(['review' => $body, 'rating' => 5]);
        }
    }

    protected function schemaFor(TestResponse $response): array
    {
        /** @var string[] $schema */
        $schema = $response->viewData('page')['props']['meta']['schema'];

        return $schema;
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
