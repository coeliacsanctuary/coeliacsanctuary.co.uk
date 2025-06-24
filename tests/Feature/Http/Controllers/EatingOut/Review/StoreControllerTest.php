<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\Review;

use App\Actions\EatingOut\CreateEateryReviewAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\EateryCreateReviewRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected EateryArea $area;

    protected Eatery $eatery;

    protected NationwideBranch $nationwideBranch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();
        $this->area = $this->create(EateryArea::class, ['town_id' => $this->town->id]);

        $this->eatery = $this->create(Eatery::class, [
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
        ]);

        $this->nationwideBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
            'name' => 'My Branch',
            'town_id' => $this->town->id,
            'county_id' => $this->county->id,
        ]);
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itReturnsNotFoundForAnEateryThatDoesntExist(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->post($route($this, 'foo'))->assertNotFound();
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itReturnsNotFoundForAnEateryThatIsNotLive(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $eatery = $this->build(Eatery::class)->notLive()->create();

        $this->post($route($this, $eatery->slug))->assertNotFound();
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithoutAnInvalidRating(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data(['rating' => null], $this)->create())
            ->assertSessionHasErrors('rating');

        $this->submitForm($route, $data(['rating' => 'foo'], $this)->create())
            ->assertSessionHasErrors('rating');

        $this->submitForm($route, $data(['rating' => true], $this)->create())
            ->assertSessionHasErrors('rating');

        $this->submitForm($route, $data(['rating' => -1], $this)->create())
            ->assertSessionHasErrors('rating');

        $this->submitForm($route, $data(['rating' => 6], $this)->create())
            ->assertSessionHasErrors('rating');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itFailsWithAnInvalidMethodValue(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data(['method' => null], $this)->create())
            ->assertSessionHasErrors('method');

        $this->submitForm($route, $data(['method' => 123], $this)->create())
            ->assertSessionHasErrors('method');

        $this->submitForm($route, $data(['method' => false], $this)->create())
            ->assertSessionHasErrors('method');

        $this->submitForm($route, $data(['method' => 'foo'], $this)->create())
            ->assertSessionHasErrors('method');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithoutABranchNameWhenTheEateryIsNationwide(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->eatery->county->update(['county' => 'Nationwide']);

        $this->submitForm($route, $data(['branch_name' => 123], $this)->create())
            ->assertSessionHasErrors('branch_name');

        $this->submitForm($route, $data(['branch_name' => false], $this)->create())
            ->assertSessionHasErrors('branch_name');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithoutAName(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data([], $this)->state(['name' => null])->create())
            ->assertSessionHasErrors('name');

        $this->submitForm($route, $data([], $this)->state(['name' => 123])->create())
            ->assertSessionHasErrors('name');

        $this->submitForm($route, $data([], $this)->state(['name' => true])->create())
            ->assertSessionHasErrors('name');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithoutAEmail(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data([], $this)->state(['email' => null])->create())
            ->assertSessionHasErrors('email');

        $this->submitForm($route, $data([], $this)->state(['email' => 123])->create())
            ->assertSessionHasErrors('email');

        $this->submitForm($route, $data([], $this)->state(['email' => true])->create())
            ->assertSessionHasErrors('email');

        $this->submitForm($route, $data([], $this)->state(['email' => 'foo'])->create())
            ->assertSessionHasErrors('email');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithoutAReviewField(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data([], $this)->state(['review' => null])->create())
            ->assertSessionHasErrors('review');

        $this->submitForm($route, $data([], $this)->state(['review' => 123])->create())
            ->assertSessionHasErrors('review');

        $this->submitForm($route, $data([], $this)->state(['review' => true])->create())
            ->assertSessionHasErrors('review');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithAnInvalidFoodRatingValue(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data([], $this)->state(['food_rating' => 123])->create())
            ->assertSessionHasErrors('food_rating');

        $this->submitForm($route, $data([], $this)->state(['food_rating' => true])->create())
            ->assertSessionHasErrors('food_rating');

        $this->submitForm($route, $data([], $this)->state(['food_rating' => 'foo'])->create())
            ->assertSessionHasErrors('food_rating');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithAnInvalidServiceRatingValue(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data([], $this)->state(['service_rating' => 123])->create())
            ->assertSessionHasErrors('service_rating');

        $this->submitForm($route, $data([], $this)->state(['service_rating' => true])->create())
            ->assertSessionHasErrors('service_rating');

        $this->submitForm($route, $data([], $this)->state(['service_rating' => 'foo'])->create())
            ->assertSessionHasErrors('service_rating');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsWithoutAnInvalidHowExpensiveField(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data(['how_expensive' => 'foo'], $this)->create())
            ->assertSessionHasErrors('how_expensive');

        $this->submitForm($route, $data(['how_expensive' => true], $this)->create())
            ->assertSessionHasErrors('how_expensive');

        $this->submitForm($route, $data(['how_expensive' => -1], $this)->create())
            ->assertSessionHasErrors('how_expensive');

        $this->submitForm($route, $data(['how_expensive' => 6], $this)->create())
            ->assertSessionHasErrors('how_expensive');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsIfSubmittingMoreThan6Images(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data(['images' => [1, 2, 3, 4, 5, 6, 7]], $this)->create())
            ->assertSessionHasErrors('images');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itErrorsIfAnImageDoesntExistInTheTable(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data(['images' => [1]], $this)->create())
            ->assertSessionHasErrors('images.0');
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itReturnsOk(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->submitForm($route, $data([], $this)->create())->assertSessionHasNoErrors();
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itExecutesDetermineNationwideBranchFromNamePipeline(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->expectPipelineToRun(DetermineNationwideBranchFromNamePipeline::class);

        $this->submitForm($route, $data(['rating' => 5], $this)->create());
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itCallsTheCreateEateryReviewAction(callable $route, callable $data, callable $before): void
    {
        $before($this);

        $this->expectAction(CreateEateryReviewAction::class);

        $this->submitForm($route, $data(['rating' => 5], $this)->create());
    }

    #[Test]
    #[DataProvider('routesToVisit')]
    public function itCreatesAFullRatingThatIsNotApproved(callable $route, callable $data, callable $before, callable $after): void
    {
        $before($this);

        $this->assertEmpty($this->eatery->reviews);

        $this->submitForm($route, $data([], $this)
            ->state([
                'rating' => 4,
                'name' => 'Foo Bar',
                'email' => 'foo@bar.com',
            ])
            ->create());

        $this->assertNotEmpty($this->eatery->reviews()->withoutGlobalScopes()->get());

        $review = EateryReview::query()->withoutGlobalScopes()->first();

        $this->assertFalse($review->approved);
        $this->assertEquals(4, $review->rating);
        $this->assertEquals('Foo Bar', $review->name);
        $this->assertEquals('foo@bar.com', $review->email);

        $after($this, $review);
    }

    protected function submitForm(callable $route, array $data): TestResponse
    {
        return $this->post($route($this), $data);
    }

    public static function routesToVisit(): array
    {
        return [
            'normal eatery' => [
                fn (self $test, ?string $eatery = null): string => route('eating-out.show.reviews.create', [
                    'county' => $test->county->slug,
                    'town' => $test->town->slug,
                    'eatery' => $eatery ?? $test->eatery->slug,
                ]),
                fn (array $data = []): EateryCreateReviewRequestFactory => EateryCreateReviewRequestFactory::new($data),
                function (): void {},
                function (self $test, EateryReview $review): void {
                    $test->assertNull($review->branch_name);
                    $test->assertNull($review->nationwide_branch_id);
                },
            ],
            'london eatery' => [
                fn (self $test, ?string $eatery = null): string => route('eating-out.london.borough.area.show.reviews.create', [
                    'town' => $test->town->slug,
                    'area' => $test->area->slug,
                    'eatery' => $eatery ?? $test->eatery->slug,
                ]),
                fn (array $data = []): EateryCreateReviewRequestFactory => EateryCreateReviewRequestFactory::new($data),
                function (self $test): void {
                    $test->eatery->update(['area_id' => $test->area->id]);
                },
                function (self $test, EateryReview $review): void {
                    $test->assertNull($review->branch_name);
                    $test->assertNull($review->nationwide_branch_id);
                },
            ],
            'nationwide eatery' => [
                fn (self $test, ?string $eatery = null): string => route('eating-out.nationwide.show.reviews.create', [
                    'eatery' => $eatery ?? $test->eatery->slug,
                ]),
                function (array $data = []): EateryCreateReviewRequestFactory {
                    $request = EateryCreateReviewRequestFactory::new($data);

                    if ( ! Arr::hasAny($data, ['nationwide_branch', 'branch_name'])) {
                        $request = $request->withBranchName();
                    }

                    return $request;
                },
                function (self $test): void {
                    $test->county->update(['county' => 'Nationwide']);
                    $test->town->update(['town' => 'nationwide']);
                },
                function (): void {},
            ],
            'nationwide branch' => [
                fn (self $test, ?string $eatery = null): string => route('eating-out.nationwide.show.branch.reviews.create', [
                    'eatery' => $eatery ?? $test->eatery->slug,
                    'nationwideBranch' => $test->nationwideBranch->slug,
                ]),
                function (array $data, self $test): EateryCreateReviewRequestFactory {
                    $request = EateryCreateReviewRequestFactory::new($data);

                    if ( ! Arr::hasAny($data, ['nationwide_branch', 'branch_name'])) {
                        $request = $request->withBranchName($test->nationwideBranch->branch_name);
                    }

                    return $request;
                },
                function (self $test): void {
                    $test->county->update(['county' => 'Nationwide']);
                    $test->town->update(['town' => 'nationwide']);
                },
                function (): void {},
            ],
        ];
    }
}
