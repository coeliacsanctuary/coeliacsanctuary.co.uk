<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\LoadCompleteEateryDetailsForRequestAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryReviewImage;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class LoadCompleteEateryDetailsForRequestActionTest extends TestCase
{
    protected Eatery $eatery;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
        $this->eatery = $this->create(Eatery::class);

        $this->create(EateryCounty::class, ['county' => 'Nationwide']);
        $this->create(EateryTown::class, ['town' => 'nationwide']);

        Eatery::preventLazyLoading();
    }

    #[Test]
    public function itSetsTheCountyRelationOfThePassedInCountyIfItIsANormalEatery(): void
    {
        $this->eatery->setRelation('county', null);
        $this->assertNull($this->eatery->county);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->county);
        $this->assertTrue($this->eatery->county()->first()->is($this->eatery->county));
    }

    #[Test]
    public function itSetsTheTownRelationOfThePassedInTownIfItIsANormalEatery(): void
    {
        $this->eatery->setRelation('town', null);
        $this->assertNull($this->eatery->town);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->town);
        $this->assertTrue($this->eatery->town()->first()->is($this->eatery->town));
    }

    #[Test]
    public function itRelatesTheNationwideCountyToTheEateryIfItIsANationwideEatery(): void
    {
        $this->eatery->setRelation('county', null);
        $this->assertNull($this->eatery->county);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
            'nationwide'
        );

        $this->assertNotNull($this->eatery->county);
        $this->assertFalse($this->eatery->county()->first()->is($this->eatery->county));
        $this->assertEquals('Nationwide', $this->eatery->county->county);
    }

    #[Test]
    public function itRelatesTheNationwideTownToTheEateryIfItIsANationwideEatery(): void
    {
        $this->eatery->setRelation('town', null);
        $this->assertNull($this->eatery->town);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
            'nationwide'
        );

        $this->assertNotNull($this->eatery->town);
        $this->assertFalse($this->eatery->town()->first()->is($this->eatery->town));
        $this->assertEquals('nationwide', $this->eatery->town->town);
    }

    #[Test]
    public function itRelatesTheNationwideCountyToTheEateryIfItIsANationwideBranch(): void
    {
        $this->eatery->setRelation('county', null);
        $this->assertNull($this->eatery->county);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]),
            'branch'
        );

        $this->assertNotNull($this->eatery->county);
        $this->assertFalse($this->eatery->county()->first()->is($this->eatery->county));
        $this->assertEquals('Nationwide', $this->eatery->county->county);
    }

    #[Test]
    public function itRelatesTheNationwideTownToTheEateryIfItIsANationwideBranch(): void
    {
        $this->eatery->setRelation('town', null);
        $this->assertNull($this->eatery->town);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]),
            'branch'
        );

        $this->assertNotNull($this->eatery->town);
        $this->assertFalse($this->eatery->town()->first()->is($this->eatery->town));
        $this->assertEquals('nationwide', $this->eatery->town->town);
    }

    #[Test]
    public function itLoadsTheAdminReviewRelationship(): void
    {
        $review = $this->build(EateryReview::class)->adminReview()->on($this->eatery)->create();

        $this->eatery->setRelation('adminReview', null);
        $this->assertNull($this->eatery->adminReview);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->adminReview);
        ;
        $this->assertTrue($review->is($this->eatery->adminReview));
    }

    #[Test]
    public function itLoadsTheReviewImagesRelationship(): void
    {
        $image = $this->build(EateryReviewImage::class)->on($this->eatery)->create();

        $this->eatery->setRelation('reviewImages', null);
        $this->assertNull($this->eatery->adminReview);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->reviewImages);
        $this->assertTrue($image->is($this->eatery->reviewImages->first()));
    }

    #[Test]
    public function itLoadsTheRestaurantsRelationship(): void
    {
        $restaurant = $this->build(EateryAttractionRestaurant::class)->on($this->eatery)->create();

        $this->eatery->setRelation('restaurants', null);
        $this->assertNull($this->eatery->restaurants);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->restaurants);
        $this->assertTrue($restaurant->is($this->eatery->restaurants->first()));
    }

    #[Test]
    public function itLoadsTheFeaturesRelationship(): void
    {
        $this->eatery->setRelation('features', null);
        $this->assertNull($this->eatery->features);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->features);
    }

    #[Test]
    public function itLoadsTheOpeningTimesRelationship(): void
    {
        $openingTimes = $this->build(EateryOpeningTimes::class)->forEatery($this->eatery)->create();

        $this->eatery->setRelation('openingTimes', null);
        $this->assertNull($this->eatery->openingTimes);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->openingTimes);
        $this->assertTrue($openingTimes->is($this->eatery->openingTimes));
    }

    #[Test]
    public function itLoadsTheReviewsRelationship(): void
    {
        $review = $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->create();

        $this->eatery->setRelation('reviews', null);
        $this->assertNull($this->eatery->reviews);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->reviews);
        $this->assertTrue($review->is($this->eatery->reviews->first()));
    }

    #[Test]
    public function itDoesntLoadNotApprovedInTheReviewsRelationship(): void
    {
        $this->build(EateryReview::class)
            ->on($this->eatery)
            ->create();

        $review = $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->create();

        $this->eatery->setRelation('reviews', null);
        $this->assertNull($this->eatery->reviews);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->reviews);
        $this->assertCount(1, $this->eatery->reviews);
        $this->assertTrue($review->is($this->eatery->reviews->first()));
    }

    #[Test]
    public function itDoesntLoadNotAdminReviewsInTheReviewsRelationship(): void
    {
        $this->build(EateryReview::class)
            ->adminReview()
            ->on($this->eatery)
            ->create();

        $review = $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->create();

        $this->eatery->setRelation('reviews', null);
        $this->assertNull($this->eatery->reviews);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            new NationwideBranch(),
        );

        $this->assertNotNull($this->eatery->reviews);
        $this->assertCount(1, $this->eatery->reviews);
        $this->assertTrue($review->is($this->eatery->reviews->first()));
    }

    #[Test]
    public function itWillOnlyLoadReviewsForTheGivenBranchOnTheBranchPageByDefault(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->create();

        $review = $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->branch($branch)
            ->create();

        $this->eatery->setRelation('reviews', null);
        $this->assertNull($this->eatery->reviews);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $branch,
            'branch',
        );

        $this->assertNotNull($this->eatery->reviews);
        $this->assertCount(1, $this->eatery->reviews);
        $this->assertTrue($review->is($this->eatery->reviews->first()));
    }

    #[Test]
    public function itCanLoadAllReviewsForTheGivenBranchOnTheBranchPage(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $review = $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->create();

        $this->build(EateryReview::class)
            ->approved()
            ->on($this->eatery)
            ->branch($branch)
            ->create();

        $this->eatery->setRelation('reviews', null);
        $this->assertNull($this->eatery->reviews);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $branch,
            'branch',
            true,
        );

        $this->assertNotNull($this->eatery->reviews);
        $this->assertCount(2, $this->eatery->reviews);
        $this->assertTrue($review->is($this->eatery->reviews->first()));
    }

    #[Test]
    public function itSetsTheGivenBranchToTheEateryInBranchMode(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->eatery->setRelation('branch', null);
        $this->assertNull($this->eatery->branch);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $branch,
            'branch',
            true,
        );

        $this->assertNotNull($this->eatery->branch);
        $this->assertTrue($branch->is($this->eatery->branch));
    }

    #[Test]
    public function itErrorsIfTheGivenBranchDoesntBelongToTheGivenEateryInBranchMode(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->build(Eatery::class)]);

        $this->expectException(NotFoundHttpException::class);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $branch,
            'branch',
            true,
        );
    }

    #[Test]
    public function ifThePageTypeIsNationwideItSetsTheBranchRelationAsNull(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->eatery->setRelation('branch', $branch);
        $this->assertNotNull($this->eatery->branch);

        app(LoadCompleteEateryDetailsForRequestAction::class)->handle(
            $this->eatery,
            $this->eatery->county()->first(),
            $this->eatery->town()->first(),
            $branch,
            'nationwide',
        );

        $this->assertNull($this->eatery->branch);
    }
}
