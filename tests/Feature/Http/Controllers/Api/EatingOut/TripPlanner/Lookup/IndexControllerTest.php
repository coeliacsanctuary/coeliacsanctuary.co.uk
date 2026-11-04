<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\TripPlanner\Lookup;

use App\Actions\EatingOut\TripPlanner\AutocompleteLocationAction;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\TripPlannerLookupRequestFactory;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected function makeRequest(array $params = []): TestResponse
    {
        return $this->postJson(route('api.wheretoeat.trip-planner.lookup'), TripPlannerLookupRequestFactory::new()->create($params));
    }

    #[Test]
    public function itReturnsAValidationErrorWithoutATerm(): void
    {
        $this->dontExpectAction(AutocompleteLocationAction::class);

        $this->makeRequest(['term' => null])->assertJsonValidationErrorFor('term');
    }

    #[Test]
    public function itReturnsAValidationErrorWithoutASessionToken(): void
    {
        $this->dontExpectAction(AutocompleteLocationAction::class);

        $this->makeRequest(['sessionToken' => null])->assertJsonValidationErrorFor('sessionToken');
    }

    #[Test]
    public function itReturnsTheLocationsFromTheAutocompleteAction(): void
    {
        $results = [['id' => 'place-1', 'label' => 'Ennis, Co. Clare, Ireland']];

        $this->expectAction(AutocompleteLocationAction::class, ['ennis', 'session-token'], return: $results);

        $this->makeRequest(['term' => 'ennis', 'sessionToken' => 'session-token'])
            ->assertOk()
            ->assertExactJson(['data' => $results]);
    }
}
