<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\UpdateTripAction;
use App\Models\EatingOut\EateryTrip;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Testing\TestResponse;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\TripPlannerRequestFactory;
use Tests\TestCase;

class UpdateControllerTest extends TestCase
{
    protected EateryTrip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trip = $this->create(EateryTrip::class);
    }

    protected function makeRequest(array $params = [], ?string $token = null, bool $withToken = true): TestResponse
    {
        if ($withToken) {
            $this->withCookie("trip-planner-{$this->trip->key}", $token ?? $this->trip->edit_token);
        }

        return $this->patch(
            route('eating-out.trip-planner.update', ['eateryTrip' => $this->trip->key]),
            [...TripPlannerRequestFactory::new()->create(), ...$params],
        );
    }

    #[Test]
    public function itIsForbiddenWithoutTheEditToken(): void
    {
        $this->dontExpectAction(UpdateTripAction::class);

        $this->makeRequest(withToken: false)->assertForbidden();
    }

    #[Test]
    public function itIsForbiddenWithAWrongToken(): void
    {
        $this->dontExpectAction(UpdateTripAction::class);

        $this->makeRequest(token: 'not-the-token')->assertForbidden();
    }

    public static function invalidPayloads(): array
    {
        return [
            'points missing' => [['points' => null], 'points'],
            'only one point' => [['points' => [['label' => 'Crewe', 'lat' => 53.1, 'lng' => -2.44]]], 'points'],
            'point without a label' => [['points' => [['lat' => 53.1, 'lng' => -2.44], ['label' => 'Manchester', 'lat' => 53.48, 'lng' => -2.24]]], 'points.0.label'],
            'distance over ten miles' => [['distance' => 11], 'distance'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPayloads')]
    public function itReturnsAValidationErrorForAnInvalidPayload(array $params, string $field): void
    {
        $this->dontExpectAction(UpdateTripAction::class);

        $this->makeRequest($params)->assertSessionHasErrors($field);
    }

    #[Test]
    public function itUpdatesTheTripWithThePointsAndDistance(): void
    {
        $this->expectAction(UpdateTripAction::class, [
            Mockery::on(fn (EateryTrip $trip): bool => $trip->is($this->trip)),
            [
                ['label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44],
                ['label' => 'Manchester, England, United Kingdom', 'lat' => 53.48, 'lng' => -2.24],
            ],
            3,
        ], return: $this->trip);

        $this->makeRequest(['distance' => 3]);
    }

    #[Test]
    public function itRedirectsToTheTrip(): void
    {
        $this->expectAction(UpdateTripAction::class, return: $this->trip);

        $this->makeRequest()->assertRedirect(route('eating-out.trip-planner.show', ['eateryTrip' => $this->trip->key]));
    }

    #[Test]
    public function itReturnsAnErrorWhenTheRouteCannotBePlanned(): void
    {
        $this->expectAction(UpdateTripAction::class, then: fn ($expectation) => $expectation->andThrow(new RequestException(new Response(new Psr7Response(500)))));

        $this->from(route('eating-out.trip-planner.show', ['eateryTrip' => $this->trip->key]))
            ->makeRequest()
            ->assertRedirect(route('eating-out.trip-planner.show', ['eateryTrip' => $this->trip->key]))
            ->assertSessionHasErrors(['route' => 'Sorry, there was an error planning your trip, please try again.']);
    }
}
