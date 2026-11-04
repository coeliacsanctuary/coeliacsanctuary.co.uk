<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner;

use App\Models\EatingOut\EateryTrip;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
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

    protected function visitPage(?string $key = null): TestResponse
    {
        return $this->get(route('eating-out.trip-planner.show', ['eateryTrip' => $key ?? $this->trip->key]));
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
        Http::fake(['api.openrouteservice.org/*' => Http::response(status: 500)]);

        $this->expectPipelineToRun(TripEateriesPipeline::class, collect());

        $this->visitPage()->assertOk();

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'api.openrouteservice.org'));
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
}
