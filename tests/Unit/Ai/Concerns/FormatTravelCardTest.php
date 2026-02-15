<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Concerns;

use App\Ai\Concerns\FormatTravelCard;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormatTravelCardTest extends TestCase
{
    #[Test]
    public function itFormatsAStandardTravelCard(): void
    {
        $category = $this->create(ShopCategory::class, ['title' => 'Coeliac Gluten Free Travel Cards']);
        $product = $this->create(ShopProduct::class, [
            'title' => 'Spanish Card',
            'description' => 'A Spanish travel card',
        ]);
        $product->categories()->attach($category);
        $this->create(ShopProductVariant::class, ['product_id' => $product->id]);
        $this->create(ShopPrice::class, [
            'purchasable_type' => ShopProduct::class,
            'purchasable_id' => $product->id,
            'price' => 500,
        ]);

        $product->load(['categories', 'reviews', 'prices', 'variants']);

        $formatter = new class () {
            use FormatTravelCard;

            public function format(ShopProduct $product): array
            {
                return $this->formatTravelCard($product);
            }
        };

        $result = $formatter->format($product);

        $this->assertEquals('Spanish Card', $result['title']);
        $this->assertEquals('A Spanish travel card', $result['description']);
        $this->assertArrayHasKey('link', $result);
        $this->assertArrayHasKey('price', $result);
        $this->assertEquals('Standard', $result['type']);
        $this->assertArrayHasKey('rating', $result);
        $this->assertArrayHasKey('average', $result['rating']);
        $this->assertArrayHasKey('count', $result['rating']);
    }

    #[Test]
    public function itFormatsACoeliaPlusTravelCard(): void
    {
        $category = $this->create(ShopCategory::class, ['title' => 'Coeliac+ Other Allergen Travel Cards']);
        $product = $this->create(ShopProduct::class, ['title' => 'French Plus Card']);
        $product->categories()->attach($category);
        $this->create(ShopProductVariant::class, ['product_id' => $product->id]);
        $this->create(ShopPrice::class, [
            'purchasable_type' => ShopProduct::class,
            'purchasable_id' => $product->id,
            'price' => 700,
        ]);

        $product->load(['categories', 'reviews', 'prices', 'variants']);

        $formatter = new class () {
            use FormatTravelCard;

            public function format(ShopProduct $product): array
            {
                return $this->formatTravelCard($product);
            }
        };

        $result = $formatter->format($product);

        $this->assertEquals('Coeliac+', $result['type']);
    }
}
