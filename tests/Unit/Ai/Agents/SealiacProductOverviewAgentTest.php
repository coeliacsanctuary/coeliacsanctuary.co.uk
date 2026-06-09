<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\SealiacProductOverviewAgent;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SealiacProductOverviewAgentTest extends TestCase
{
    protected function makeAgent(ShopProduct $product): SealiacProductOverviewAgent
    {
        return new SealiacProductOverviewAgent($product);
    }

    #[Test]
    public function itRendersTheIntroductionText(): void
    {
        $product = $this->create(ShopProduct::class);

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringContainsString('Sealiac the Seal', $result);
        $this->assertStringContainsString('Coeliac Sanctuary', $result);
    }

    #[Test]
    public function itRendersProductDetails(): void
    {
        $product = $this->create(ShopProduct::class);

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringContainsString('Product Title:', $result);
        $this->assertStringContainsString($product->title, $result);
    }

    #[Test]
    public function itRendersAverageRatingWhenReviewsExist(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->create(['rating' => 4]);

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringContainsString('Average Rating:', $result);
    }

    #[Test]
    public function itDoesNotRenderAverageRatingWhenNoReviewsExist(): void
    {
        $product = $this->create(ShopProduct::class);

        ShopOrderReviewItem::query()->where('product_id', $product->id)->delete();

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringNotContainsString('Average Rating:', $result);
    }

    #[Test]
    public function itRendersPreviousReviewsWhenPresent(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->create([
            'review' => 'Great product!',
        ]);

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringContainsString('## Previous Purchaser Reviews', $result);
        $this->assertStringContainsString('Great product!', $result);
    }

    #[Test]
    public function itDoesNotRenderPreviousReviewsSectionWhenNoneExist(): void
    {
        $product = $this->create(ShopProduct::class);

        ShopOrderReviewItem::query()->where('product_id', $product->id)->delete();

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringNotContainsString('## Previous Purchaser Reviews', $result);
    }

    #[Test]
    public function itRendersASeparatorBetweenMultipleReviews(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->count(2)->create();

        $result = (string) $this->makeAgent($product)->instructions();

        $this->assertStringContainsString('------', $result);
    }
}
