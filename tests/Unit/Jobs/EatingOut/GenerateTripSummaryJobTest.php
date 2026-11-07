<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Ai\Agents\TripSummaryAgent;
use App\Enums\EatingOut\TripSummaryStatus;
use App\Jobs\EatingOut\GenerateTripSummaryJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryTrip;
use App\Models\EatingOut\EateryVenueType;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Bus;
use Laravel\Ai\Prompts\AgentPrompt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateTripSummaryJobTest extends TestCase
{
    protected EateryTrip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->travelTo('2026-11-01 10:00:00');

        $this->trip = $this->create(EateryTrip::class, ['stop_interval' => 60, 'ai_summary_status' => TripSummaryStatus::PENDING]);

        $this->travelTo('2026-11-01 10:05:00');
    }

    protected function eatery(array $attributes = []): JsonResource
    {
        return new JsonResource([
            'name' => 'Gluten Free Cafe',
            'chain' => null,
            'venueTypeId' => EateryVenueType::query()->first()->id,
            'typeId' => 1,
            'town' => 'Crewe',
            'rating' => 4.5,
            'reviewsCount' => 12,
            'durationAlongRoute' => 3500.0,
            ...$attributes,
        ]);
    }

    protected function expectEateries(JsonResource ...$eateries): void
    {
        $this->expectPipelineToRun(TripEateriesPipeline::class, collect($eateries));
    }

    protected function expectStops(): void
    {
        $this->expectAction(GetTripStopsAction::class, return: [
            ['number' => 1, 'duration' => 3600.0, 'distanceAlongRoute' => 80467.2, 'lat' => 52.85, 'lng' => -2.15],
        ]);
    }

    protected function fakeSummary(array $stops = [['number' => 1, 'summary' => 'Stop one is near Stafford.']]): void
    {
        TripSummaryAgent::fake([['overview' => 'A lovely drive.', 'stops' => $stops]]);
    }

    #[Test]
    public function itPromptsTheAgentWithTheRouteAndTheBestRatedPlaces(): void
    {
        $this->expectEateries($this->eatery(), $this->eatery(['name' => 'Unrated Pub', 'rating' => null, 'reviewsCount' => 0]));
        $this->expectStops();
        $this->fakeSummary();

        app()->call([new GenerateTripSummaryJob($this->trip), 'handle']);

        TripSummaryAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_contains($prompt->prompt, 'Route: Crewe, England, United Kingdom → Manchester, England, United Kingdom')
            && str_contains($prompt->prompt, 'Places to eat along the route: 2')
            && str_contains($prompt->prompt, "Best rated places along the route:\n- Gluten Free Cafe")
            && str_contains($prompt->prompt, 'rated 4.5 from 12 reviews'));
    }

    #[Test]
    public function itPromptsTheAgentWithEachStopsNearestTownAndNearbyPlaces(): void
    {
        $town = $this->create(EateryTown::class, ['town' => 'Stafford', 'latlng' => '52.8064,-2.1171', 'county_id' => 1]);
        $this->create(Eatery::class, ['town_id' => $town->id]);

        $this->expectEateries($this->eatery(['name' => 'Near The Stop']), $this->eatery(['name' => 'Far Away', 'durationAlongRoute' => 9000.0]));
        $this->expectStops();
        $this->fakeSummary();

        app()->call([new GenerateTripSummaryJob($this->trip), 'handle']);

        TripSummaryAgent::assertPrompted(function (AgentPrompt $prompt): bool {
            $stop = str($prompt->prompt)->after('Stop 1');

            return $stop->contains('Nearest town: Stafford, Cheshire')
                && $stop->contains('Into the drive: 1h 0m, 50 miles')
                && $stop->contains('Near The Stop')
                && $stop->doesntContain('Far Away');
        });
    }

    #[Test]
    public function itStoresTheSummaryAndMarksItReady(): void
    {
        $this->expectEateries($this->eatery());
        $this->expectStops();
        $this->fakeSummary();

        app()->call([new GenerateTripSummaryJob($this->trip), 'handle']);

        $this->trip->refresh();

        $this->assertSame('A lovely drive.', $this->trip->ai_summary['overview']);
        $this->assertSame([[1, 'Stop one is near Stafford.']], collect($this->trip->ai_summary['stops'])->map(fn (array $stop): array => [$stop['number'], $stop['summary']])->all());
        $this->assertSame(TripSummaryStatus::READY, $this->trip->ai_summary_status);
    }

    #[Test]
    public function itDropsSummariesForStopsThatDontExist(): void
    {
        $this->expectEateries($this->eatery());
        $this->expectStops();
        $this->fakeSummary([['number' => 1, 'summary' => 'Stop one.'], ['number' => 2, 'summary' => 'Made up stop.'], ['number' => 1, 'summary' => 'Stop one again.']]);

        app()->call([new GenerateTripSummaryJob($this->trip), 'handle']);

        $this->assertSame([[1, 'Stop one.']], collect($this->trip->refresh()->ai_summary['stops'])->map(fn (array $stop): array => [$stop['number'], $stop['summary']])->all());
    }

    #[Test]
    public function itDoesntMoveTheTripsUpdatedTimeOn(): void
    {
        $this->expectEateries($this->eatery());
        $this->expectStops();
        $this->fakeSummary();

        app()->call([new GenerateTripSummaryJob($this->trip), 'handle']);

        $this->assertSame('2026-11-01 10:00:00', $this->trip->refresh()->updated_at?->toDateTimeString());
    }

    #[Test]
    public function itTellsTheAgentThereAreNoStopsWhenTheTripHasNone(): void
    {
        $this->trip->update(['stop_interval' => null]);

        $this->expectEateries($this->eatery());
        $this->fakeSummary([]);

        app()->call([new GenerateTripSummaryJob($this->trip), 'handle']);

        TripSummaryAgent::assertPrompted(fn (AgentPrompt $prompt): bool => str_ends_with($prompt->prompt, 'Stops: none')
            && ! str_contains($prompt->prompt, 'Stop 1'));

        $this->assertSame([], $this->trip->refresh()->ai_summary['stops']);
    }

    #[Test]
    public function itDoesNothingIfTheTripHasBeenEditedSinceItWasDispatched(): void
    {
        $job = new GenerateTripSummaryJob($this->trip);

        $this->trip->update(['distance' => 5]);

        TripSummaryAgent::fake()->preventStrayPrompts();

        app()->call($job->handle(...));

        TripSummaryAgent::assertNeverPrompted();
        $this->assertSame(TripSummaryStatus::PENDING, $this->trip->refresh()->ai_summary_status);
    }

    #[Test]
    public function itMarksTheSummaryAsFailedWhenTheJobFails(): void
    {
        new GenerateTripSummaryJob($this->trip)->failed();

        $this->assertSame(TripSummaryStatus::FAILED, $this->trip->refresh()->ai_summary_status);
    }

    #[Test]
    public function itDoesntMarkANewerSummaryAsFailed(): void
    {
        $job = new GenerateTripSummaryJob($this->trip);

        $this->trip->update(['distance' => 5]);

        $job->failed();

        $this->assertSame(TripSummaryStatus::PENDING, $this->trip->refresh()->ai_summary_status);
    }
}
