<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Ai\Prompts;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Ai\Prompts\EatingOutSealiacOverviewPrompt;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EatingOutSealiacOverviewPromptTest extends TestCase
{
    #[Test]
    public function itRendersTheIntroductionText(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Sealiac the Seal', $result);
        $this->assertStringContainsString('Coeliac Sanctuary', $result);
    }

    #[Test]
    public function itRendersEateryDetails(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class)->load(['area', 'town', 'county', 'country']);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('## Eatery Details', $result);
        $this->assertStringContainsString($eatery->name, $result);
        $this->assertStringContainsString($eatery->full_location, $result);
    }

    #[Test]
    public function itRendersAverageExpenseWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'how_expensive' => 3,
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Average Value for Money Rating: ' . EateryReview::HOW_EXPENSIVE_LABELS[3], $result);
    }

    #[Test]
    public function itDoesNotRenderAverageExpenseWhenAbsent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('Average Value for Money Rating', $result);
    }

    #[Test]
    public function itRendersAverageRatingWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'rating' => 5,
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Average Rating: 5 out of 5 stars', $result);
    }

    #[Test]
    public function itDoesNotRenderAverageRatingWhenAbsent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('Average Rating:', $result);
    }

    // --- adminReview() helper ---

    #[Test]
    public function itReturnsNullAdminReviewWhenNone(): void
    {
        $eatery = $this->create(Eatery::class);

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery->load(['adminReview']);

        $this->assertNull($prompt->adminReview());
    }

    #[Test]
    public function itReturnsNullAdminReviewForWrongBranch(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $branch1 = $this->create(NationwideBranch::class, ['wheretoeat_id' => $eatery->id]);
        $branch2 = $this->create(NationwideBranch::class, ['wheretoeat_id' => $eatery->id]);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->adminReview()
            ->branch($branch1)
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery->load(['adminReview']);
        $prompt->branch = $branch2;

        $this->assertNull($prompt->adminReview());
    }

    #[Test]
    public function itReturnsAdminReviewForCorrectBranch(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $eatery->id]);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->adminReview()
            ->branch($branch)
            ->create();

        $prompt = invade(new EatingOutSealiacOverviewPrompt());
        $prompt->eatery = $eatery->load(['adminReview']);
        $prompt->branch = $branch;

        $this->assertInstanceOf(EateryReview::class, $prompt->adminReview());
    }

    // --- Admin review rendering ---

    #[Test]
    public function itRendersAdminReviewWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->adminReview()
            ->create();

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('## Coeliac Sanctuary Team Review', $result);
    }

    #[Test]
    public function itDoesNotRenderAdminReviewWhenAbsent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('## Coeliac Sanctuary Team Review', $result);
    }

    #[Test]
    public function itDoesNotRenderAdminReviewForWrongBranch(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $branch1 = $this->create(NationwideBranch::class, ['wheretoeat_id' => $eatery->id])->load(['area', 'town', 'county', 'country']);
        $branch2 = $this->create(NationwideBranch::class, ['wheretoeat_id' => $eatery->id])->load(['area', 'town', 'county', 'country']);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->adminReview()
            ->branch($branch1)
            ->create();

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery, $branch2);

        $this->assertStringNotContainsString('## Coeliac Sanctuary Team Reviews', $result);
    }

    // --- Visitor review rendering ---

    #[Test]
    public function itRendersVisitorReviewsWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->approved()
            ->create();

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('## Website Visitor Reviews', $result);
    }

    #[Test]
    public function itDoesNotRenderVisitorReviewsWhenNone(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('## Website Visitor Reviews', $result);
    }

    #[Test]
    public function itDoesNotRenderVisitorReviewsWhenOnlyAdminReview(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->adminReview()
            ->create();

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('## Website Visitor Reviews', $result);
    }

    #[Test]
    public function itRendersASeparatorBetweenMultipleVisitorReviews(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->build(EateryReview::class)
            ->on($eatery)
            ->count(2)
            ->approved()
            ->create();

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('------', $result);
    }

    #[Test]
    public function itRendersServiceRatingInReviewWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'service_rating' => 'excellent',
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Service Rating: excellent', $result);
    }

    #[Test]
    public function itDoesNotRenderServiceRatingInReviewWhenAbsent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'service_rating' => null,
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('Service Rating:', $result);
    }

    #[Test]
    public function itRendersFoodRatingInReviewWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'food_rating' => 'excellent',
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Food Rating: excellent', $result);
    }

    #[Test]
    public function itDoesNotRenderFoodRatingInReviewWhenAbsent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'food_rating' => null,
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('Food Rating:', $result);
    }

    #[Test]
    public function itRendersValueForMoneyInReviewWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'how_expensive' => 3,
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Value for Money: ' . EateryReview::HOW_EXPENSIVE_LABELS[3], $result);
    }

    #[Test]
    public function itDoesNotRenderValueForMoneyInReviewWhenAbsent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'how_expensive' => null,
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('Value for Money:', $result);
    }

    #[Test]
    public function itRendersBranchNameInReviewWhenNoBranchSet(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'branch_name' => 'My Branch',
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('Branch Name: My Branch', $result);
    }

    #[Test]
    public function itDoesNotRenderBranchNameInReviewWhenBranchIsSet(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $eatery->id])->load(['area', 'town', 'county', 'country']);

        $this->create(EateryReview::class, [
            'wheretoeat_id' => $eatery->id,
            'nationwide_branch_id' => $branch->id,
            'branch_name' => 'My Branch',
            'approved' => true,
        ]);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery, $branch);

        $this->assertStringNotContainsString('Branch Name:', $result);
    }

    // --- Features rendering ---

    #[Test]
    public function itRendersFeaturesWhenPresent(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $features = $this->build(EateryFeature::class)->count(2)->create();
        $eatery->features()->attach($features);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringContainsString('## Features of this eatery listed on our website:', $result);
        $this->assertStringContainsString("- {$features->first()->feature}", $result);
        $this->assertStringContainsString("- {$features->last()->feature}", $result);
    }

    #[Test]
    public function itDoesNotRenderFeaturesWhenNone(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);
        $eatery = $this->create(Eatery::class);

        $result = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery);

        $this->assertStringNotContainsString('## Features of this eatery listed on our website:', $result);
    }

    // --- eateryName() helper ---

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

    // --- eateryLocation() helper ---

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
