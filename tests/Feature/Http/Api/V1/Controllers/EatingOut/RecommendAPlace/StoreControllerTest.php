<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\RecommendAPlace;

use App\Actions\EatingOut\CreatePlaceRecommendationAction;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\Api\V1\RecommendAPlaceRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->create(EateryVenueType::class);
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->postJson(route('api.v1.eating-out.recommend-a-place.store'))->assertForbidden();
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
    public function itErrorsWithAMissingOrInvalidPlace(): void
    {
        $this->makeRequest(['place' => null])->assertJsonValidationErrorFor('place');
        $this->makeRequest(['place' => true])->assertJsonValidationErrorFor('place');
        $this->makeRequest(['place' => 123])->assertJsonValidationErrorFor('place');
        $this->makeRequest(['place' => 'foo'])->assertJsonValidationErrorFor('place');
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidPlaceName(): void
    {
        $this->makeRequest(['place.name' => null])->assertJsonValidationErrorFor('place.name');
        $this->makeRequest(['place.name' => true])->assertJsonValidationErrorFor('place.name');
        $this->makeRequest(['place.name' => 123])->assertJsonValidationErrorFor('place.name');
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidPlaceLocation(): void
    {
        $this->makeRequest(['place.location' => null])->assertJsonValidationErrorFor('place.location');
        $this->makeRequest(['place.location' => true])->assertJsonValidationErrorFor('place.location');
        $this->makeRequest(['place.location' => 123])->assertJsonValidationErrorFor('place.location');
    }

    #[Test]
    public function itErrorsWithAMissingOrInvalidPlaceDetails(): void
    {
        $this->makeRequest(['place.details' => null])->assertJsonValidationErrorFor('place.details');
        $this->makeRequest(['place.details' => true])->assertJsonValidationErrorFor('place.details');
        $this->makeRequest(['place.details' => 123])->assertJsonValidationErrorFor('place.details');
    }

    #[Test]
    public function itErrorsWithAnInvalidPlaceUrl(): void
    {
        $this->makeRequest(['place.url' => true])->assertJsonValidationErrorFor('place.url');
        $this->makeRequest(['place.url' => 123])->assertJsonValidationErrorFor('place.url');
        $this->makeRequest(['place.url' => 'foo'])->assertJsonValidationErrorFor('place.url');
    }

    #[Test]
    public function itErrorsWithAnInvalidPlaceVenueType(): void
    {
        $this->makeRequest(['place.venueType' => true])->assertJsonValidationErrorFor('place.venueType');
        $this->makeRequest(['place.venueType' => 123])->assertJsonValidationErrorFor('place.venueType');
        $this->makeRequest(['place.venueType' => 'foo'])->assertJsonValidationErrorFor('place.venueType');
    }

    #[Test]
    public function itCallsTheCreatePlaceRecommendationAction(): void
    {
        $this->mock(CreatePlaceRecommendationAction::class)
            ->shouldReceive('handle')
            ->once();

        $this->makeRequest()->assertCreated();
    }

    protected function makeRequest(array $params = [], string $source = 'bar'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.recommend-a-place.store'),
            RecommendAPlaceRequestFactory::new()->create($params),
            ['x-coeliac-source' => $source],
        );
    }
}
