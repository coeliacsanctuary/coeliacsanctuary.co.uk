<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Ai\Prompts;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Ai\Prompts\EatingOutSealiacOverviewPrompt;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EatingOutSealiacOverviewPromptTest extends TestCase
{
    #[Test]
    public function theMainHandleMethodGoesThroughEachExpectedMethodFlow(): void
    {
        $eatery = $this->create(Eatery::class);

        $prompt = $this->partialMock(EatingOutSealiacOverviewPrompt::class);

        $prompt
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('preparePromptIntroduction')
            ->once()
            ->getMock()
            ->shouldReceive('addBaseEateryDetails')
            ->once()
            ->getMock()
            ->shouldReceive('addAdminReviewIfAvailable')
            ->once()
            ->getMock()
            ->shouldReceive('addVisitorReviews')
            ->once()
            ->getMock()
            ->shouldReceive('addEateryFeatures')
            ->once();

        $prompt->handle($eatery);
    }

    #[Test]
    public function preparePromptIntroductionLoadsTheFirstBlockOntoThePromptCollection(): void
    {
        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->preparePromptIntroduction();

        $this->assertCount(1, $prompt->promptSections);
    }

    #[Test]
    public function addBaseEateryDetailsPushesTheBasicEateryInfoOnToThePrompt(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class)->load(['area', 'town', 'county', 'country']);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addBaseEateryDetails();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertEquals('## Eatery Details', $promptSection[0]);
        $this->assertStringContainsString($eatery->name, $promptSection[1]);
        $this->assertStringContainsString($eatery->full_location, $promptSection[2]);
    }

    #[Test]
    public function addBaseEateryDetailsIncludesTheAverageExpenseIfOneIsPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class)->load(['area', 'town', 'county', 'country']);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'how_expensive' => 3,
            'approved' => true,
        ]);

        $eatery->load(['reviews']);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addBaseEateryDetails();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertStringContainsString(EateryReview::HOW_EXPENSIVE_LABELS[3], $promptSection[3]);
    }

    #[Test]
    public function addBaseEateryDetailsIncludesTheAverageRatingIfOneIsPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class)->load(['area', 'town', 'county', 'country']);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'rating' => 5,
            'approved' => true,
        ]);

        $eatery->load(['reviews']);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addBaseEateryDetails();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertStringContainsString('5 out of 5 stars', $promptSection[4]);
    }

    #[Test]
    public function formatReviewReturnsAnArrayOfTheBaseReviewData(): void
    {
        $review = $this->create(EateryReview::class, [
            'service_rating' => null,
            'food_rating' => null,
            'how_expensive' => null,
            'rating' => 5,
            'review' => 'foo bar baz',
            'approved' => true,
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());

        $reviewArray = $prompt->formatReview($review);

        $this->assertCount(5, $reviewArray);

        $this->assertStringContainsString('5 out of 5 stars', $reviewArray[0]);
        $this->assertEquals('', $reviewArray[1]);
        $this->assertEquals('foo bar baz', $reviewArray[2]);
        $this->assertEquals('', $reviewArray[3]);
        $this->assertStringContainsString('Published: ', $reviewArray[4]);
    }

    #[Test]
    public function formatReviewIncludesTheServiceRatingIfItIsPresent(): void
    {
        $review = $this->create(EateryReview::class, [
            'service_rating' => 'excellent',
            'food_rating' => null,
            'how_expensive' => null,
            'rating' => 5,
            'review' => 'foo bar baz',
            'approved' => true,
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());

        $reviewArray = $prompt->formatReview($review);

        $this->assertCount(6, $reviewArray);

        $this->assertEquals('Service Rating: excellent', $reviewArray[0]);
    }

    #[Test]
    public function formatReviewIncludesTheFoodRatingIfItIsPresent(): void
    {
        $review = $this->create(EateryReview::class, [
            'service_rating' => null,
            'food_rating' => 'excellent',
            'how_expensive' => null,
            'rating' => 5,
            'review' => 'foo bar baz',
            'approved' => true,
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());

        $reviewArray = $prompt->formatReview($review);

        $this->assertCount(6, $reviewArray);

        $this->assertEquals('Food Rating: excellent', $reviewArray[0]);
    }

    #[Test]
    public function formatReviewIncludesTheValueForMoneyIfItIsPresent(): void
    {
        $review = $this->create(EateryReview::class, [
            'service_rating' => null,
            'food_rating' => null,
            'how_expensive' => 3,
            'rating' => 5,
            'review' => 'foo bar baz',
            'approved' => true,
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());

        $reviewArray = $prompt->formatReview($review);

        $this->assertCount(6, $reviewArray);

        $this->assertStringContainsString(EateryReview::HOW_EXPENSIVE_LABELS[3], $reviewArray[0]);
    }

    #[Test]
    public function addAdminReviewDoesntDoAnythingIfTheresNoReviews(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addAdminReviewIfAvailable();

        $this->assertEmpty($prompt->promptSections);
    }

    #[Test]
    public function addAdminReviewDoesntDoAnythingIfTheresNoAdminReview(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->approved()
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addAdminReviewIfAvailable();

        $this->assertEmpty($prompt->promptSections);
    }

    #[Test]
    public function addAdminReviewAddsAFormattedReviewToThePrompt(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->approved()
            ->adminReview()
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addAdminReviewIfAvailable();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertEquals('## Coeliac Sanctuary Team Reviews', $promptSection[0]);
        $this->assertEquals('', $promptSection[1]);
        $this->assertNotEquals('', $promptSection[2]);
    }

    #[Test]
    public function addVisitorReviewDoesntDoAnythingIfTheresNoReviews(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addVisitorReviews();

        $this->assertEmpty($prompt->promptSections);
    }

    #[Test]
    public function addVisitorReviewDoesntDoAnythingIfTheresOnlyAnAdminReview(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->approved()
            ->adminReview()
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addVisitorReviews();

        $this->assertEmpty($prompt->promptSections);
    }

    #[Test]
    public function addVisitorReviewAddsAFormattedReviewToThePrompt(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->approved()
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addVisitorReviews();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertEquals('## Website Visitor Reviews', $promptSection[0]);
        $this->assertEquals('', $promptSection[1]);
        $this->assertNotEquals('', $promptSection[2]);
    }

    #[Test]
    public function addVisitorReviewAddsALineBetweenIndividualReviews(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->count(2)
            ->approved()
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addVisitorReviews();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertContains('------', $promptSection);
    }

    #[Test]
    public function addEateryFeaturesDoesNothingIfTheresNoFeaturesOnTheEatery(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addEateryFeatures();

        $this->assertEmpty($prompt->promptSections);
    }

    #[Test]
    public function addEateryFeaturesListsEachFeatureInTheEatery(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $features = $this->build(EateryFeature::class)->count(2)->create();

        $eatery->features()->attach($features);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->promptSections = collect();

        $this->assertEmpty($prompt->promptSections);

        $prompt->addEateryFeatures();

        $this->assertCount(1, $prompt->promptSections);

        $firstSection = $prompt->promptSections->first();

        $this->assertIsString($firstSection);

        $promptSection = Str::explode($firstSection, "\n");

        $this->assertEquals('## Features of this eatery listed on our website:', $promptSection[0]);
        $this->assertEquals('', $promptSection[1]);
        $this->assertEquals("- {$features->first()->feature}", $promptSection[2]);
        $this->assertEquals("- {$features->second()->feature}", $promptSection[3]);
    }

    #[Test]
    public function itCanGetTheBranchNameWhenABranchIsSetAndItHasAName(): void
    {
        $eatery = $this->create(Eatery::class);
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'name' => 'foo bar baz',
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->branch = $branch;

        $this->assertEquals('foo bar baz', $prompt->eateryName());
    }

    #[Test]
    public function itGetsTheEateryNameWhenThereIsABranchButItHasNoName(): void
    {
        $eatery = $this->create(Eatery::class, [
            'name' => 'My Eatery',
        ]);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'name' => null,
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;
        $prompt->branch = $branch;

        $this->assertEquals('My Eatery', $prompt->eateryName());
    }

    #[Test]
    public function itGetsTheEateryNameWhenThereIsOnlyAnEatery(): void
    {
        $eatery = $this->create(Eatery::class, [
            'name' => 'My Eatery',
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery;

        $this->assertEquals('My Eatery', $prompt->eateryName());
    }

    #[Test]
    public function itCanGetTheBranchLocationWhenABranchIsSet(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'name' => 'foo bar baz',
        ]);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery->load(['area', 'town', 'county', 'country']);
        $prompt->branch = $branch->load(['area', 'town', 'county', 'country']);

        $this->assertEquals($branch->full_location, $prompt->eateryLocation());
    }

    #[Test]
    public function itGetsTheEateryLocationWhenThereIsOnlyAnEatery(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery->load(['area', 'town', 'county', 'country']);

        $this->assertEquals($eatery->full_location, $prompt->eateryLocation());
    }
}
