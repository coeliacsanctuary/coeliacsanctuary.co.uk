<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Builders\EatingOut\EateryBuilder;
use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\DataObjects\EatingOut\TripRoute;
use App\DataObjects\EatingOut\TripRouteChunk;
use App\Models\EatingOut\Eatery;
use App\Support\Helpers;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class GetEateriesAlongRouteAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->route instanceof TripRoute || ! $pipelineData->routeRadius) {
            throw_if($pipelineData->throwSearchException, new RuntimeException('No Route'));

            return $next($pipelineData);
        }

        $radius = Helpers::milesToMeters($pipelineData->routeRadius);
        $chunks = $pipelineData->route->chunks($radius);

        $query = Eatery::query()
            ->select(['id', 'lat', 'lng', 'type_id', 'venue_type_id'])
            ->where('closed_down', false)
            ->whereRaw('(select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat.county_id) != ?', ['Nationwide'])
            ->where(function (EateryBuilder $query) use ($chunks): void {
                $chunks->each(fn (TripRouteChunk $chunk) => $query->orWhere(fn (EateryBuilder $query) => $query
                    ->whereRaw('cast(lat as decimal(10, 7)) between ? and ?', array_values($chunk->latBounds))
                    ->whereRaw('cast(lng as decimal(10, 7)) between ? and ?', array_values($chunk->lngBounds))));
            });

        if (Arr::has($pipelineData->filters, 'categories') && $pipelineData->filters['categories'] !== null) {
            $query = $query->hasCategories($pipelineData->filters['categories']);
        }

        if (Arr::has($pipelineData->filters, 'venueTypes') && $pipelineData->filters['venueTypes'] !== null) {
            $query = $query->hasVenueTypes($pipelineData->filters['venueTypes']);
        }

        if (Arr::has($pipelineData->filters, 'features') && $pipelineData->filters['features'] !== null) {
            $query = $query->hasFeatures($pipelineData->filters['features']);
        }

        /** @var EloquentCollection<int, Eatery> $eateries */
        $eateries = $query->get();

        $pendingEateries = $eateries
            ->map(fn (Eatery $eatery): array => [$eatery, $pipelineData->route->locate($chunks, (float) $eatery->lat, (float) $eatery->lng)])
            ->filter(fn (array $located): bool => $located[1]['distance'] <= $radius)
            ->map(fn (array $located): PendingEatery => new PendingEatery(
                id: $located[0]->id,
                branchId: null,
                ordering: $located[1]['along'],
                lat: $located[0]->lat,
                lng: $located[0]->lng,
                distance: $located[1]['distance'],
                typeId: $located[0]->type_id,
                venueTypeId: $located[0]->venue_type_id,
                durationAlongRoute: $located[1]['duration'],
            ));

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }
}
