<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Ai\Prompts;

use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Support\Ai\Prompts\ShopProductSealiacOverviewPrompt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopProductSealiacOverviewPromptTest extends TestCase
{
    protected function renderPrompt(ShopProduct $product): string
    {
        $product->load(['reviews']);

        return app(ShopProductSealiacOverviewPrompt::class)->handle($product);
    }

    #[Test]
    public function itRendersTheIntroductionText(): void
    {
        $product = $this->create(ShopProduct::class);

        $output = $this->renderPrompt($product);

        $this->assertStringContainsString('Sealiac the Seal', $output);
        $this->assertStringContainsString('Coeliac Sanctuary', $output);
    }

    #[Test]
    public function itRendersProductDetails(): void
    {
        $product = $this->create(ShopProduct::class, [
            'long_description' => 'foo bar baz',
        ]);

        $output = $this->renderPrompt($product);

        $this->assertStringContainsString($product->title, $output);
        $this->assertStringContainsString('foo bar baz', $output);
        $this->assertStringContainsString('## Product Details', $output);
    }

    #[Test]
    public function itRendersAverageRatingWhenPresent(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->createQuietly([
            'rating' => 4,
        ]);

        $output = $this->renderPrompt($product);

        $this->assertStringContainsString('out of 5 stars', $output);
        $this->assertStringContainsString('Average Rating:', $output);
        $this->assertStringContainsString('Total reviews: 1', $output);
    }

    #[Test]
    public function itDoesNotRenderAverageRatingWhenAbsent(): void
    {
        $product = $this->create(ShopProduct::class);

        $output = $this->renderPrompt($product);

        $this->assertStringNotContainsString('Average Rating:', $output);
    }

    #[Test]
    public function itRendersCustomerReviews(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->createQuietly([
            'review' => 'Great product!',
        ]);

        $output = $this->renderPrompt($product);

        $this->assertStringContainsString('## Previous Purchaser Reviews', $output);
        $this->assertStringContainsString('Great product!', $output);
    }

    #[Test]
    public function itDoesNotRenderReviewsSectionWhenThereAreNoReviews(): void
    {
        $product = $this->create(ShopProduct::class);

        $output = $this->renderPrompt($product);

        $this->assertStringNotContainsString('## Previous Purchaser Reviews', $output);
    }

    #[Test]
    public function itRendersASeparatorBetweenMultipleReviews(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->count(2)->createQuietly();

        $output = $this->renderPrompt($product);

        $this->assertStringContainsString('------', $output);
    }
}
