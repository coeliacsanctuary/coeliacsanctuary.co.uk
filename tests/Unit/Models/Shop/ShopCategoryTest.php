<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopCategoryTest extends TestCase
{
    #[Test]
    public function itHasALiveScope(): void
    {
        $this->assertNotEmpty(ShopCategory::query()->toBase()->wheres);
    }

    #[Test]
    public function itCanHaveMedia(): void
    {
        Storage::fake('media');

        $category = $this->create(ShopCategory::class);

        $category->addMedia(UploadedFile::fake()->image('social.jpg'))->toMediaCollection('social');
        $category->addMedia(UploadedFile::fake()->image('primary.jpg'))->toMediaCollection('primary');

        $this->assertCount(2, $category->media);
    }

    #[Test]
    public function itCanGenerateALink(): void
    {
        $category = $this->create(ShopCategory::class, [
            'slug' => 'test-category',
        ]);

        $this->assertEquals('/shop/test-category', $category->link);
    }

    #[Test]
    public function itResolvesItsRouteKeyToTheSlug(): void
    {
        $category = $this->create(ShopCategory::class, [
            'slug' => 'test-category',
        ]);

        $this->assertEquals('test-category', $category->getRouteKey());
        $this->assertEquals(route('shop.category', 'test-category'), route('shop.category', $category));
    }

    #[Test]
    public function itHasManyProducts(): void
    {
        ShopCategory::withoutGlobalScopes();
        ShopProduct::withoutGlobalScopes();
        ShopProductVariant::withoutGlobalScopes();

        $category = $this->create(ShopCategory::class);

        $products = $this->build(ShopProduct::class)
            ->count(5)
            ->create();

        $category->products()->attach($products->pluck('id')->toArray());

        $this->assertCount(5, $category->products()->withoutGlobalScopes()->get());
    }
}
