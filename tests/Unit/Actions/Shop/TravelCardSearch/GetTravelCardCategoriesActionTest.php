<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\GetTravelCardCategoriesAction;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopCategoryIndexResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTravelCardCategoriesActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(2, 2, 1);

        /** @var ShopCategory $coeliacPlus */
        $coeliacPlus = $this->create(ShopCategory::class, ['id' => 11, 'title' => 'Coeliac+']);

        /** @var ShopProduct $product */
        $product = ShopProduct::query()->orderBy('id')->get()->last();

        $coeliacPlus->products()->attach($product);
    }

    protected function categories(): AnonymousResourceCollection
    {
        return $this->callAction(GetTravelCardCategoriesAction::class);
    }

    #[Test]
    public function itOnlyReturnsTheTwoTravelCardCategories(): void
    {
        $categories = $this->categories();

        $this->assertCount(2, $categories->collection);

        $categories->collection->each(function ($category): void {
            $this->assertInstanceOf(ShopCategoryIndexResource::class, $category);
        });
    }

    #[Test]
    public function itPutsTheStandardCardsBeforeCoeliacPlus(): void
    {
        $this->assertEquals(
            [1, 11],
            $this->categories()->collection->pluck('id')->all(),
        );
    }

    #[Test]
    public function itCountsTheProductsInEachCategory(): void
    {
        $categories = $this->categories()->collection;

        $this->assertEquals(2, $categories->first()->products_count);
        $this->assertEquals(1, $categories->last()->products_count);
    }

    #[Test]
    public function itIncludesACategoryPrice(): void
    {
        $resolved = $this->categories()->collection->first()->toArray(new Request());

        $this->assertStringStartsWith('from £', (string) $resolved['price']);
    }
}
