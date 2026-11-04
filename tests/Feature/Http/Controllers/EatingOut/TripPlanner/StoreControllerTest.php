<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\CreateTripAction;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\TripPlannerRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function makeRequest(array $params = []): TestResponse
    {
        return $this->post(route('eating-out.trip-planner.store'), [...TripPlannerRequestFactory::new()->create(), ...$params]);
    }

    public static function invalidPayloads(): array
    {
        return [
            'points missing' => [['points' => null], 'points'],
            'only one point' => [['points' => [['label' => 'Crewe', 'lat' => 53.1, 'lng' => -2.44]]], 'points'],
            'more than five via points' => [['points' => array_fill(0, 8, ['label' => 'Crewe', 'lat' => 53.1, 'lng' => -2.44])], 'points'],
            'point without a label' => [['points' => [['lat' => 53.1, 'lng' => -2.44], ['label' => 'Manchester', 'lat' => 53.48, 'lng' => -2.24]]], 'points.0.label'],
            'point without a lat' => [['points' => [['label' => 'Crewe', 'lng' => -2.44], ['label' => 'Manchester', 'lat' => 53.48, 'lng' => -2.24]]], 'points.0.lat'],
            'point with a non numeric lng' => [['points' => [['label' => 'Crewe', 'lat' => 53.1, 'lng' => 'foo'], ['label' => 'Manchester', 'lat' => 53.48, 'lng' => -2.24]]], 'points.0.lng'],
            'distance missing' => [['distance' => null], 'distance'],
            'distance under a mile' => [['distance' => 0.5], 'distance'],
            'distance over ten miles' => [['distance' => 11], 'distance'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPayloads')]
    public function itReturnsAValidationErrorForAnInvalidPayload(array $params, string $field): void
    {
        $this->dontExpectAction(CreateTripAction::class);

        $this->makeRequest($params)->assertSessionHasErrors($field);
    }

    #[Test]
    public function itCreatesTheTripFromThePointsAndDistance(): void
    {
        $this->expectAction(CreateTripAction::class, [
            [
                ['label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44],
                ['label' => 'Manchester, England, United Kingdom', 'lat' => 53.48, 'lng' => -2.24],
            ],
            2,
        ], return: $this->create(EateryTrip::class));

        $this->makeRequest();
    }

    #[Test]
    public function itRedirectsToTheTrip(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->expectAction(CreateTripAction::class, return: $trip);

        $this->makeRequest()->assertRedirect(route('eating-out.trip-planner.show', ['eateryTrip' => $trip->key]));
    }

    #[Test]
    public function itStoresTheEditTokenInACookieForTheTrip(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->expectAction(CreateTripAction::class, return: $trip);

        $this->makeRequest()->assertCookie("trip-planner-{$trip->key}", $trip->edit_token);
    }

    #[Test]
    public function itReturnsAnErrorWhenTheRouteCannotBePlanned(): void
    {
        $this->expectAction(CreateTripAction::class, then: fn ($expectation) => $expectation->andThrow(new RequestException(new Response(new Psr7Response(500)))));

        $this->from(route('eating-out.trip-planner'))
            ->makeRequest()
            ->assertRedirect(route('eating-out.trip-planner'))
            ->assertSessionHasErrors(['route' => 'Sorry, there was an error planning your trip, please try again.']);
    }

    #[Test]
    public function itCreatesTheTripThroughEveryViaPointInOrder(): void
    {
        $points = [
            ['label' => 'Crewe, UK', 'lat' => 53.1, 'lng' => -2.44],
            ['label' => 'Leeds, UK', 'lat' => 53.8, 'lng' => -1.55],
            ['label' => 'York, UK', 'lat' => 53.96, 'lng' => -1.08],
            ['label' => 'Whitby, UK', 'lat' => 54.49, 'lng' => -0.61],
        ];

        $this->expectAction(CreateTripAction::class, [
            $points,
            2,
        ], return: $this->create(EateryTrip::class));

        $this->makeRequest(['points' => $points]);
    }
}
