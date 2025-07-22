<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Ai\Prompts;

use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Support\Ai\Prompts\ShopProductSealiacOverviewPrompt;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopProductSealiacOverviewPromptTest extends TestCase
{
    #[Test]
    public function theMainHandleMethodGoesThroughEachExpectedMethodFlow(): void
    {
        $product = $this->create(ShopProduct::class);

        $prompt = $this->partialMock(ShopProductSealiacOverviewPrompt::class);

        $prompt
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('preparePromptIntroduction')
            ->once()
            ->getMock()
            ->shouldReceive('addBaseProductInformation')
            ->once()
            ->getMock()
            ->shouldReceive('addCustomerReviews')
            ->once();

        $prompt->handle($product);
    }

    #[Test]
    public function preparePromptIntroductionLoadsTheFirstBlockOntoThePromptCollection(): void
    {
        $prompt = invade(new ShopProductSealiacOverviewPrompt());
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->preparePromptIntroduction();

        $this->assertCount(1, $prompt->promptSections);
    }

    #[Test]
    public function addBaseProductInformationPushesTheBasicProductInfoOnToThePrompt(): void
    {
        $product = $this->create(ShopProduct::class, [
            'long_description' => 'foo bar baz',
        ]);

        $prompt = invade(new ShopProductSealiacOverviewPrompt());
        $prompt->product = $product;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addBaseProductInformation();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertEquals('## Product Details', $promptSection[0]);
        $this->assertStringContainsString($product->title, $promptSection[1]);
        $this->assertStringContainsString($product->long_description, $promptSection[2]);
    }

    #[Test]
    public function addBaseEateryDetailsIncludesTheAverageRatingIfOneIsPresent(): void
    {
        $product = $this->create(ShopProduct::class, [
            'long_description' => 'foo bar baz',
        ]);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->createQuietly([
            'rating' => 5,
        ]);

        $product->load(['reviews']);

        $prompt = invade(new ShopProductSealiacOverviewPrompt());
        $prompt->product = $product;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addBaseProductInformation();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertStringContainsString('5 out of 5 stars', $promptSection[3]);
        $this->assertStringContainsString('Total reviews: 1', $promptSection[4]);
    }

    #[Test]
    public function formatReviewReturnsAnArrayOfTheBaseReviewData(): void
    {
        $review = $this->build(ShopOrderReviewItem::class)->createQuietly([
            'review' => 'foo bar baz',
            'rating' => 5,
        ]);

        $prompt = invade(new ShopProductSealiacOverviewPrompt());

        $reviewArray = $prompt->formatReview($review);

        $this->assertCount(5, $reviewArray);

        $this->assertStringContainsString('5 out of 5 stars', $reviewArray[0]);
        $this->assertEquals('', $reviewArray[1]);
        $this->assertEquals('foo bar baz', $reviewArray[2]);
        $this->assertEquals('', $reviewArray[3]);
        $this->assertStringContainsString('Published: ', $reviewArray[4]);
    }

    #[Test]
    public function addVisitorReviewAddsAFormattedReviewToThePrompt(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->createQuietly();

        $prompt = invade(new ShopProductSealiacOverviewPrompt());
        $prompt->product = $product;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addCustomerReviews();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertEquals('## Previous Purchaser Reviews', $promptSection[0]);
        $this->assertEquals('', $promptSection[1]);
        $this->assertNotEquals('', $promptSection[2]);
    }

    #[Test]
    public function addVisitorReviewAddsALineBetweenIndividualReviews(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(ShopOrderReviewItem::class)->forProduct($product)->count(2)->createQuietly();


        $prompt = invade(new ShopProductSealiacOverviewPrompt());
        $prompt->product = $product;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addCustomerReviews();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertContains('------', $promptSection);
    }
}
