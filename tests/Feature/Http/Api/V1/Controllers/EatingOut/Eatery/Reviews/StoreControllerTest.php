<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews;

use App\Actions\EatingOut\CreateEateryReviewAction;
use App\Models\EatingOut\Eatery;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\Api\V1\CreateReviewRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->postJson(route('api.v1.eating-out.details.reviews.store', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidRating(): void
    {
        $this->makeRequest(['rating' => null])->assertJsonValidationErrorFor('rating');
        $this->makeRequest(['rating' => true])->assertJsonValidationErrorFor('rating');
        $this->makeRequest(['rating' => 'foo'])->assertJsonValidationErrorFor('rating');
        $this->makeRequest(['rating' => -1])->assertJsonValidationErrorFor('rating');
        $this->makeRequest(['rating' => 6])->assertJsonValidationErrorFor('rating');
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidName(): void
    {
        $this->makeRequest(['name' => null])->assertJsonValidationErrorFor('name');
        $this->makeRequest(['name' => true])->assertJsonValidationErrorFor('name');
        $this->makeRequest(['name' => 123])->assertJsonValidationErrorFor('name');
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidEmail(): void
    {
        $this->makeRequest(['email' => null])->assertJsonValidationErrorFor('email');
        $this->makeRequest(['email' => true])->assertJsonValidationErrorFor('email');
        $this->makeRequest(['email' => 123])->assertJsonValidationErrorFor('email');
        $this->makeRequest(['email' => 'foo'])->assertJsonValidationErrorFor('email');
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidReview(): void
    {
        $this->makeRequest(['review' => null])->assertJsonValidationErrorFor('review');
        $this->makeRequest(['review' => true])->assertJsonValidationErrorFor('review');
        $this->makeRequest(['review' => 123])->assertJsonValidationErrorFor('review');
        $this->makeRequest(['review' => Str::random(1501)])->assertJsonValidationErrorFor('review');
    }

    #[Test]
    public function itErrorsWithAnInvalidFoodRating(): void
    {
        $this->makeRequest(['food_rating' => true])->assertJsonValidationErrorFor('food_rating');
        $this->makeRequest(['food_rating' => 123])->assertJsonValidationErrorFor('food_rating');
        $this->makeRequest(['food_rating' => 'foo'])->assertJsonValidationErrorFor('food_rating');
    }

    #[Test]
    public function itErrorsWithAnInvalidServiceRating(): void
    {
        $this->makeRequest(['service_rating' => true])->assertJsonValidationErrorFor('service_rating');
        $this->makeRequest(['service_rating' => 123])->assertJsonValidationErrorFor('service_rating');
        $this->makeRequest(['service_rating' => 'foo'])->assertJsonValidationErrorFor('service_rating');
    }

    #[Test]
    public function itErrorsWithAnInvalidExpenseRating(): void
    {
        $this->makeRequest(['how_expensive' => true])->assertJsonValidationErrorFor('how_expensive');
        $this->makeRequest(['how_expensive' => 'foo'])->assertJsonValidationErrorFor('how_expensive');
        $this->makeRequest(['how_expensive' => -1])->assertJsonValidationErrorFor('how_expensive');
        $this->makeRequest(['how_expensive' => 6])->assertJsonValidationErrorFor('how_expensive');
    }

    #[Test]
    public function itErrorsIfImagesIsntAnArray(): void
    {
        $this->makeRequest(['images' => true])->assertJsonValidationErrorFor('images');
        $this->makeRequest(['images' => 123])->assertJsonValidationErrorFor('images');
        $this->makeRequest(['images' => 'foo'])->assertJsonValidationErrorFor('images');
    }

    #[Test]
    public function itErrorsIfTheImageIsntAString(): void
    {
        $this->makeRequest(['images' => [true]])->assertJsonValidationErrorFor('images.0');
        $this->makeRequest(['images' => [123]])->assertJsonValidationErrorFor('images.0');
        $this->makeRequest(['images' => ['foo']])->assertJsonValidationErrorFor('images.0');
    }

    #[Test]
    public function itErrorsWithAnInvalidMethod(): void
    {
        $this->makeRequest(['method' => true])->assertJsonValidationErrorFor('method');
        $this->makeRequest(['method' => 123])->assertJsonValidationErrorFor('method');
        $this->makeRequest(['method' => 'foo'])->assertJsonValidationErrorFor('method');
    }

    #[Test]
    public function itErrorsWithoutBranchInformationIfTheEateryIsNationwide(): void
    {
        $this->eatery->county->update(['county' => 'Nationwide']);

        $this->makeRequest()
            ->assertJsonValidationErrorFor('branch_id')
            ->assertJsonValidationErrorFor('branch_name');
    }

    #[Test]
    public function itErrorsWithAnInvalidBranchIdIfTheEateryIsNationwide(): void
    {
        $this->eatery->county->update(['county' => 'Nationwide']);

        $this->makeRequest(['branch_id' => 'foo'])->assertJsonValidationErrorFor('branch_id');
    }

    #[Test]
    public function itErrorsWithAnInvalidBranchNameIfTheEateryIsNationwide(): void
    {
        $this->eatery->county->update(['county' => 'Nationwide']);

        $this->makeRequest(['branch_name' => true])->assertJsonValidationErrorFor('branch_name');
        $this->makeRequest(['branch_name' => 123])->assertJsonValidationErrorFor('branch_name');
    }

    #[Test]
    public function ifTheBranchIdIsFilledButItDoesntExistItReturnsNotFound(): void
    {
        $this->eatery->county->update(['county' => 'Nationwide']);

        $this->makeRequest(['branch_id' => 123])->assertNotFound();
    }

    #[Test]
    public function itCallsTheDetermineNationwideBranchFromNamePipelineIfItIsNationwideButNoBranchIdWasGiven(): void
    {
        $this->eatery->county->update(['county' => 'Nationwide']);

        $this->mock(DetermineNationwideBranchFromNamePipeline::class)
            ->shouldReceive('run')
            ->once();

        $this->makeRequest(['branch_name' => 'foo'])->assertCreated();
    }

    #[Test]
    public function itCallsTheCreateEateryReviewAction(): void
    {
        $this->mock(CreateEateryReviewAction::class)
            ->shouldReceive('handle')
            ->once();

        $this->makeRequest()->assertCreated();
    }

    protected function makeRequest(array $params = [], string $source = 'bar'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.details.reviews.store', $this->eatery),
            CreateReviewRequestFactory::new()->create($params),
            ['x-coeliac-source' => $source],
        );
    }
}
