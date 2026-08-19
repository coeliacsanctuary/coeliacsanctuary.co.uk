<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Shop;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Resources\Shop\ShopProductIndexResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopProductIndexResourceTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts(1, 1);

        $this->product = ShopProduct::query()->with(['variants', 'prices', 'reviews'])->first();
    }

    #[Test]
    public function itReturnsANullFootnoteWhenTheCategoriesRelationIsntLoaded(): void
    {
        $this->attachCountry('france');

        $this->product->load('travelCardSearchTerms');

        $resource = (new ShopProductIndexResource($this->product))->toArray(new Request());

        $this->assertNull($resource['footnote']);
    }

    #[Test]
    public function itReturnsANullFootnoteWhenTheTravelCardSearchTermsRelationIsntLoaded(): void
    {
        $this->attachCountry('france');

        $this->product->setRelation('categories', collect([ShopCategory::query()->first()]));

        $resource = (new ShopProductIndexResource($this->product))->toArray(new Request());

        $this->assertNull($resource['footnote']);
    }

    #[Test]
    public function itReturnsTheFootnoteWhenBothRelationsAreLoaded(): void
    {
        $this->attachCountry('france');

        $this->product->load('travelCardSearchTerms');
        $this->product->setRelation('categories', collect([ShopCategory::query()->first()]));

        $resource = (new ShopProductIndexResource($this->product))->toArray(new Request());

        $this->assertSame('Covers France', $resource['footnote']);
    }

    protected function attachCountry(string $term): void
    {
        $this->product->travelCardSearchTerms()->attach(
            $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => 'country']),
            ['card_show_on_product_page' => true, 'card_score' => 10, 'card_language' => 'french'],
        );
    }
}
