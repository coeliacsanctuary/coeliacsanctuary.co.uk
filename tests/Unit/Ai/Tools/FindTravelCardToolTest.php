<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\State\ChatContext;
use App\Ai\Tools\FindTravelCardTool;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindTravelCardToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsMatchingTravelCardsByCountry(): void
    {
        $category = $this->create(ShopCategory::class, ['title' => 'Coeliac Gluten Free Travel Cards']);
        $product = $this->create(ShopProduct::class, ['title' => 'Spanish Travel Card']);

        $product->categories()->attach($category);

        $this->create(ShopProductVariant::class, ['product_id' => $product->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $product->id, 'price' => 500]);

        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => 'Spain', 'type' => 'country']);
        $searchTerm->products()->attach($product);

        $tool = new FindTravelCardTool();

        $result = json_decode((string) $tool->handle(new Request(['country_or_language' => 'Spain'])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Spain', $result[0]['term']);
        $this->assertEquals('country', $result[0]['type']);
        $this->assertCount(1, $result[0]['products']);
        $this->assertEquals('Spanish Travel Card', $result[0]['products'][0]['title']);
        $this->assertEquals('Standard', $result[0]['products'][0]['type']);
    }

    #[Test]
    public function itReturnsMatchingTravelCardsByLanguage(): void
    {
        $category = $this->create(ShopCategory::class, ['title' => 'Coeliac+ Other Allergen Travel Cards']);
        $product = $this->create(ShopProduct::class, ['title' => 'French Plus Card']);

        $product->categories()->attach($category);

        $this->create(ShopProductVariant::class, ['product_id' => $product->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $product->id, 'price' => 700]);

        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => 'French', 'type' => 'language']);
        $searchTerm->products()->attach($product);

        $tool = new FindTravelCardTool();
        $result = json_decode((string) $tool->handle(new Request(['country_or_language' => 'French'])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Coeliac+', $result[0]['products'][0]['type']);
    }

    #[Test]
    public function itReturnsEmptyWhenNoMatchingTerms(): void
    {
        $tool = new FindTravelCardTool();
        $result = json_decode((string) $tool->handle(new Request(['country_or_language' => 'Klingon'])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itIncludesProductPriceAndRating(): void
    {
        $category = $this->create(ShopCategory::class, ['title' => 'Coeliac Gluten Free Travel Cards']);
        $product = $this->create(ShopProduct::class, ['title' => 'Greek Travel Card']);

        $product->categories()->attach($category);

        $this->create(ShopProductVariant::class, ['product_id' => $product->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $product->id, 'price' => 500]);

        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => 'Greece']);
        $searchTerm->products()->attach($product);

        $tool = new FindTravelCardTool();
        $result = json_decode((string) $tool->handle(new Request(['country_or_language' => 'Greece'])), true);

        $this->assertArrayHasKey('price', $result[0]['products'][0]);
        $this->assertArrayHasKey('link', $result[0]['products'][0]);
        $this->assertArrayHasKey('rating', $result[0]['products'][0]);
        $this->assertArrayHasKey('average', $result[0]['products'][0]['rating']);
        $this->assertArrayHasKey('count', $result[0]['products'][0]['rating']);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new FindTravelCardTool();
        $tool->handle(new Request(['country_or_language' => 'test']));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('FindTravelCardTool', $toolUses->first()['tool']);
    }
}
