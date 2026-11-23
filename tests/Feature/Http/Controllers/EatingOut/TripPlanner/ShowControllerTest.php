<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\FindNearestTownAction;
use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Enums\EatingOut\TripSummaryStatus;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryTrip;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use Illuminate\Testing\TestResponse;
use Mockery;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected EateryTrip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trip = $this->create(EateryTrip::class);
    }

    protected function visitPage(?string $key = null, array $query = []): TestResponse
    {
        return $this->get(route('eating-out.trip-planner.show', ['eateryTrip' => $key ?? $this->trip->key, ...$query]));
    }

    #[Test]
    public function itReturnsNotFoundForAnUnknownTrip(): void
    {
        $this->visitPage('NOTATRIP')->assertNotFound();
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itRunsTheTripEateriesPipelineWithoutCallingOpenRouteService(): void
    {
        Http::fake(['api.heigit.org/*' => Http::response(status: 500)]);

        $this->expectPipelineToRun(TripEateriesPipeline::class, collect());

        $this->visitPage()->assertOk();

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.heigit.org'));
    }

    #[Test]
    public function itRendersTheTripAndItsEateries(): void
    {
        $this->expectPipelineToRun(TripEateriesPipeline::class, collect([['id' => 1]]));

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page
            ->component('EatingOut/TripPlanner')
            ->where('trip.key', $this->trip->key)
            ->where('trip.points.0.label', 'Crewe, England, United Kingdom')
            ->where('trip.points.1.label', 'Manchester, England, United Kingdom')
            ->where('trip.distance', 2)
            ->where('trip.route.geometry', '_p~iF~ps|U_ulLnnqC_mqNvxq`@')
            ->where('trip.route.distance', 57381.5)
            ->where('trip.route.duration', 3222.9)
            ->where('eateries', [['id' => 1]])
            ->etc());
    }

    #[Test]
    public function itTellsSearchEnginesNotToIndexTheTrip(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->where('meta.doNotTrack', true)->etc());
    }

    #[Test]
    public function itRecordsTheVisit(): void
    {
        $this->freezeSecond();

        $this->visitPage();

        $this->assertSame(now()->toDateTimeString(), $this->trip->refresh()->last_visited_at?->toDateTimeString());
    }

    #[Test]
    public function itLetsTheTripBeEditedWithTheEditTokenCookie(): void
    {
        $this->withCookie("trip-planner-{$this->trip->key}", $this->trip->edit_token)
            ->visitPage()
            ->assertInertia(fn (Assert $page): Assert => $page->where('canEdit', true)->etc());
    }

    #[Test]
    public function itDoesNotLetTheTripBeEditedWithoutTheEditTokenCookie(): void
    {
        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->where('canEdit', false)->etc());
    }

    #[Test]
    public function itIncludesTheTripsStopInterval(): void
    {
        $this->trip->update(['stop_interval' => 120]);

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->where('trip.stopInterval', 120)->etc());
    }

    #[Test]
    public function itIncludesTheSuggestedStopsForTheTrip(): void
    {
        $this->trip->update(['stop_interval' => 60]);

        $stops = [['number' => 1, 'type' => 'suggested', 'label' => null, 'duration' => 3600.5, 'distanceAlongRoute' => 90000.5, 'lat' => 53.5, 'lng' => -2.3]];

        $this->expectAction(GetTripStopsAction::class, [Mockery::any(), $this->trip->points, 60], return: $stops);

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->where('stops', $stops)->etc());
    }

    #[Test]
    public function itIncludesAPendingSummaryWithoutAnyContent(): void
    {
        $this->trip->update(['ai_summary_status' => TripSummaryStatus::PENDING]);

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary', ['status' => 'pending', 'overview' => null, 'stops' => []])
            ->etc());
    }

    #[Test]
    public function itIncludesTheSummaryAsHtmlWithTheStopsKeyedByNumber(): void
    {
        $this->trip->update([
            'ai_summary' => [
                'overview' => 'A **lovely** drive.',
                'stops' => [['number' => 2, 'summary' => 'Stop two.']],
            ],
            'ai_summary_status' => TripSummaryStatus::READY,
        ]);

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page
            ->where('summary.status', 'ready')
            ->where('summary.overview', "<p>A <strong>lovely</strong> drive.</p>\n")
            ->where('summary.stops.2', "<p>Stop two.</p>\n")
            ->etc());
    }

    protected function townAtDistance(float $metres): EateryTown
    {
        $county = $this->create(EateryCounty::class, ['slug' => 'denbighshire']);

        return $this->create(EateryTown::class, ['town' => 'Llangollen', 'slug' => 'llangollen', 'county_id' => $county->id])
            ->setRelation('county', $county)
            ->setAttribute('distance', $metres);
    }

    #[Test]
    public function itLinksEachPointAfterTheStartToItsNearestTown(): void
    {
        $this->trip->update(['points' => [
            ['label' => 'Crewe', 'lat' => 53.1, 'lng' => -2.44],
            ['label' => 'Llangollen', 'lat' => 52.97, 'lng' => -3.17],
            ['label' => 'Llandudno', 'lat' => 53.32, 'lng' => -3.83],
        ]]);

        $town = $this->townAtDistance(1000);

        $this->expectAction(FindNearestTownAction::class, once: false, then: fn ($mock) => $mock
            ->twice()
            ->andReturnUsing(fn (float $lat, float $lng): ?EateryTown => [$lat, $lng] === [52.97, -3.17] ? $town : null));

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page
            ->where('towns', [null, ['name' => 'Llangollen', 'link' => '/wheretoeat/denbighshire/llangollen'], null])
            ->etc());
    }

    #[Test]
    public function itDoesntLinkATownMoreThanFiveMilesFromThePoint(): void
    {
        $this->expectAction(FindNearestTownAction::class, return: $this->townAtDistance(8100));

        $this->visitPage()->assertInertia(fn (Assert $page): Assert => $page->where('towns', [null, null])->etc());
    }

    #[Test]
    public function itFiltersTheEateriesFromTheQueryString(): void
    {
        $this->mock(TripEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(fn (...$args): bool => $args[2] === [
                'categories' => ['wte', 'att'],
                'venueTypes' => ['cafe'],
                'features' => ['takeaway'],
            ])
            ->once()
            ->andReturn(collect());

        $this->visitPage(query: ['categories' => 'wte,att', 'venueTypes' => 'cafe', 'features' => 'takeaway'])->assertOk();
    }

    #[Test]
    public function itDoesNotFilterTheEateriesWithoutAQueryString(): void
    {
        $this->mock(TripEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(fn (...$args): bool => $args[2] === ['categories' => null, 'venueTypes' => null, 'features' => null])
            ->once()
            ->andReturn(collect());

        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itUsesTheTripPlannerOpenGraphImage(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, [
            static fn (string $route, mixed $alterUrl = null): bool => $route === 'eatery-trip-planner',
        ]);

        $this->visitPage();
    }
}
