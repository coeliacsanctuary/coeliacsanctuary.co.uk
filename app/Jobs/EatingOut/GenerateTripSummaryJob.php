<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Actions\EatingOut\TripPlanner\FindNearestTownAction;
use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Ai\Agents\TripSummaryAgent;
use App\Enums\EatingOut\EateryType;
use App\Enums\EatingOut\TripSummaryStatus;
use App\Models\EatingOut\EateryTrip;
use App\Models\EatingOut\EateryVenueType;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Collection;

#[Tries(1)]
#[Timeout(300)]
class GenerateTripSummaryJob implements ShouldQueue
{
    use Queueable;

    protected string $version;

    /** @var Collection<int, string> */
    protected Collection $venueTypes;

    public function __construct(protected EateryTrip $trip)
    {
        $this->version = (string) $trip->updated_at;
    }

    public function handle(TripEateriesPipeline $tripEateriesPipeline, GetTripStopsAction $getTripStopsAction, FindNearestTownAction $findNearestTownAction): void
    {
        if ( ! $this->isCurrent()) {
            return;
        }

        $this->venueTypes = EateryVenueType::query()->pluck('venue_type', 'id');

        /** @var Collection<int, array<string, mixed>> $eateries */
        $eateries = $tripEateriesPipeline
            ->run(
                route: $this->trip->toTripRoute(),
                radius: $this->trip->distance,
                filters: ['categories' => null, 'features' => null, 'venueTypes' => null],
            )
            ->map(fn (JsonResource $eatery): array => $eatery->resolve());

        $stops = $getTripStopsAction->handle($this->trip->toTripRoute(), $this->trip->stop_interval);

        $response = TripSummaryAgent::make()->prompt($this->buildPrompt($eateries, $stops, $findNearestTownAction));

        $stopNumbers = array_column($stops, 'number');

        /** @var array<int, array{number: int, summary: string}> $summaries */
        $summaries = $response['stops'];

        $this->save([
            'ai_summary' => [
                'overview' => (string) $response['overview'],
                'stops' => collect($summaries)
                    ->filter(fn (array $stop): bool => in_array($stop['number'], $stopNumbers, true))
                    ->unique('number')
                    ->map(fn (array $stop): array => ['number' => $stop['number'], 'summary' => $stop['summary']])
                    ->values()
                    ->all(),
            ],
            'ai_summary_status' => TripSummaryStatus::READY,
        ]);
    }

    public function failed(): void
    {
        if ( ! $this->isCurrent()) {
            return;
        }

        $this->save(['ai_summary' => null, 'ai_summary_status' => TripSummaryStatus::FAILED]);
    }

    protected function isCurrent(): bool
    {
        return (string) $this->trip->updated_at === $this->version;
    }

    /** @param  array<string, mixed>  $attributes */
    protected function save(array $attributes): void
    {
        $this->trip->timestamps = false;

        $this->trip->update($attributes);

        $this->trip->timestamps = true;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $eateries
     * @param  array<int, array{number: int, duration: float, distanceAlongRoute: float, lat: float, lng: float}>  $stops
     */
    protected function buildPrompt(Collection $eateries, array $stops, FindNearestTownAction $findNearestTownAction): string
    {
        $route = collect($this->trip->points)->pluck('label')->join(' → ');
        $distance = $this->miles($this->trip->route_distance);
        $duration = $this->duration($this->trip->route_duration);

        $bestRated = $this->listEateries(
            $eateries
                ->whereNotNull('rating')
                ->sortByDesc(fn (array $eatery): array => [$eatery['rating'], $eatery['reviewsCount']])
                ->take(10),
        );

        $prompt = <<<PROMPT
        Route: {$route}
        Driving distance: {$distance}
        Driving time: {$duration}
        Places to eat along the route: {$eateries->count()}

        Best rated places along the route:
        {$bestRated}
        PROMPT;

        if ($stops === []) {
            return $prompt . "\n\nStops: none";
        }

        $stopSections = collect($stops)->map(function (array $stop) use ($eateries, $findNearestTownAction): string {
            $town = $findNearestTownAction->handle($stop['lat'], $stop['lng']);
            $location = $town ? "{$town->town}, {$town->county?->county}" : 'Unknown';
            $into = "{$this->duration($stop['duration'])}, {$this->miles($stop['distanceAlongRoute'])}";

            $nearby = $this->listEateries(
                $eateries
                    ->filter(fn (array $eatery): bool => abs($eatery['durationAlongRoute'] - $stop['duration']) <= 1800)
                    ->sortBy(fn (array $eatery): float => abs($eatery['durationAlongRoute'] - $stop['duration']))
                    ->take(10),
            );

            return <<<PROMPT
            Stop {$stop['number']}
            Nearest town: {$location}
            Into the drive: {$into}
            Places to eat within half an hour of this stop:
            {$nearby}
            PROMPT;
        });

        return $prompt . "\n\n" . $stopSections->join("\n\n");
    }

    /** @param  Collection<int, array<string, mixed>>  $eateries */
    protected function listEateries(Collection $eateries): string
    {
        if ($eateries->isEmpty()) {
            return '- None';
        }

        return $eateries
            ->map(function (array $eatery): string {
                $name = $eatery['chain'] ? "{$eatery['name']} ({$eatery['chain']})" : $eatery['name'];
                $type = $this->venueTypes->get($eatery['venueTypeId']) ?? EateryType::from($eatery['typeId'])->name();
                $rating = $eatery['rating'] !== null ? "rated {$eatery['rating']} from {$eatery['reviewsCount']} reviews" : 'no ratings yet';

                return "- {$name} ({$type}, {$eatery['town']}) - {$rating}, {$this->duration($eatery['durationAlongRoute'])} into the drive";
            })
            ->join("\n");
    }

    protected function duration(float $seconds): string
    {
        return intdiv((int) $seconds, 3600) . 'h ' . (int) round(((int) $seconds % 3600) / 60) . 'm';
    }

    protected function miles(float $metres): string
    {
        return round($metres / 1609.344) . ' miles';
    }
}
