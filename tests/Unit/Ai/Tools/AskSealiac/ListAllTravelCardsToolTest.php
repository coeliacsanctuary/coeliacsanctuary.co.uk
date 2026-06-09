<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\ListAllTravelCardsTool;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListAllTravelCardsToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsStandardAndPlusCards(): void
    {
        $standardCategory = $this->create(ShopCategory::class, ['title' => 'Coeliac Gluten Free Travel Cards']);
        $plusCategory = $this->create(ShopCategory::class, ['title' => 'Coeliac+ Other Allergen Travel Cards']);

        $standardProduct = $this->create(ShopProduct::class, ['title' => 'Spanish Card']);
        $standardProduct->categories()->attach($standardCategory);
        $this->create(ShopProductVariant::class, ['product_id' => $standardProduct->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $standardProduct->id, 'price' => 500]);

        $plusProduct = $this->create(ShopProduct::class, ['title' => 'French Plus Card']);
        $plusProduct->categories()->attach($plusCategory);
        $this->create(ShopProductVariant::class, ['product_id' => $plusProduct->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $plusProduct->id, 'price' => 700]);

        $tool = new ListAllTravelCardsTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertArrayHasKey('standard_cards', $result);
        $this->assertArrayHasKey('coeliac_plus_cards', $result);
        $this->assertCount(1, $result['standard_cards']);
        $this->assertCount(1, $result['coeliac_plus_cards']);
        $this->assertEquals('Spanish Card', $result['standard_cards'][0]['title']);
        $this->assertEquals('Standard', $result['standard_cards'][0]['type']);
        $this->assertEquals('French Plus Card', $result['coeliac_plus_cards'][0]['title']);
        $this->assertEquals('Coeliac+', $result['coeliac_plus_cards'][0]['type']);
    }

    #[Test]
    public function itReturnsEmptyArraysWhenNoCardsExist(): void
    {
        $tool = new ListAllTravelCardsTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertEmpty($result['standard_cards']);
        $this->assertEmpty($result['coeliac_plus_cards']);
    }

    #[Test]
    public function itOrdersPinnedCardsFirst(): void
    {
        $category = $this->create(ShopCategory::class, ['title' => 'Coeliac Gluten Free Travel Cards']);

        $unpinnedProduct = $this->create(ShopProduct::class, ['title' => 'A Unpinned Card', 'pinned' => false]);
        $unpinnedProduct->categories()->attach($category);
        $this->create(ShopProductVariant::class, ['product_id' => $unpinnedProduct->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $unpinnedProduct->id, 'price' => 500]);

        $pinnedProduct = $this->create(ShopProduct::class, ['title' => 'Z Pinned Card', 'pinned' => true]);
        $pinnedProduct->categories()->attach($category);
        $this->create(ShopProductVariant::class, ['product_id' => $pinnedProduct->id]);
        $this->create(ShopPrice::class, ['purchasable_type' => ShopProduct::class, 'purchasable_id' => $pinnedProduct->id, 'price' => 500]);

        $tool = new ListAllTravelCardsTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertEquals('Z Pinned Card', $result['standard_cards'][0]['title']);
        $this->assertEquals('A Unpinned Card', $result['standard_cards'][1]['title']);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new ListAllTravelCardsTool();
        $tool->handle(new Request());

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('ListAllTravelCardsTool', $toolUses->first()['tool']);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $tool = new ListAllTravelCardsTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $this->assertEmpty($tool->schema($schema));
    }
}
