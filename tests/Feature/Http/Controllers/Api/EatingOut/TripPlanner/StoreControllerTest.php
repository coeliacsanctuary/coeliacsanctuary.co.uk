<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\GetRouteAction;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use Illuminate\Support\Collection;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\TripPlannerRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function makeRequest(array $params = []): TestResponse
    {
        return $this->postJson(route('api.wheretoeat.trip-planner.store'), [...TripPlannerRequestFactory::new()->create(), ...$params]);
    }

    protected function route(): TripRoute
    {
        return new TripRoute(
            geometry: '_p~iF~ps|U',
            points: collect([new LatLng(38.5, -120.2)]),
            distance: 57381.5,
            duration: 3222.9,
            timings: [],
        );
    }

    public static function invalidPayloads(): array
    {
        return [
            'points missing' => [['points' => null], 'points'],
            'only one point' => [['points' => [['lat' => 53.1, 'lng' => -2.44]]], 'points'],
            'point without a lat' => [['points' => [['lng' => -2.44], ['lat' => 53.48, 'lng' => -2.24]]], 'points.0.lat'],
            'point with a non numeric lng' => [['points' => [['lat' => 53.1, 'lng' => 'foo'], ['lat' => 53.48, 'lng' => -2.24]]], 'points.0.lng'],
            'distance missing' => [['distance' => null], 'distance'],
            'distance under a mile' => [['distance' => 0.5], 'distance'],
            'distance over ten miles' => [['distance' => 11], 'distance'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPayloads')]
    public function itReturnsAValidationErrorForAnInvalidPayload(array $params, string $field): void
    {
        $this->dontExpectAction(GetRouteAction::class);

        $this->makeRequest($params)->assertJsonValidationErrorFor($field);
    }

    #[Test]
    public function itGetsTheRouteForThePointsInOrder(): void
    {
        $this->expectAction(GetRouteAction::class, [
            static fn (Collection $points): bool => $points->map(fn (LatLng $point): array => [$point->lat, $point->lng])->all() === [[53.1, -2.44], [53.48, -2.24]],
        ], return: $this->route());

        $this->expectPipelineToRun(TripEateriesPipeline::class, collect());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsTheRouteAndTheEateriesAlongIt(): void
    {
        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->expectPipelineToRun(TripEateriesPipeline::class, collect([['id' => 1]]));

        $this->makeRequest()
            ->assertOk()
            ->assertExactJson([
                'route' => ['geometry' => '_p~iF~ps|U', 'distance' => 57381.5, 'duration' => 3222.9],
                'eateries' => [['id' => 1]],
            ]);
    }
}
