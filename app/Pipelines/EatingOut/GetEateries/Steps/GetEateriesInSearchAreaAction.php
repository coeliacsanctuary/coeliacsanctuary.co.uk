<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Services\EatingOut\LocationSearchService;
use App\Support\Helpers;
use App\Support\State\EatingOut\Search\LatLngState;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class GetEateriesInSearchAreaAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->searchTerm || $pipelineData->searchTerm->term === '') {
            throw_if($pipelineData->throwSearchException, new RuntimeException('No Search Term'));

            return $next($pipelineData);
        }

        try {
            $latLng = $this->getLatLng($pipelineData);
        } catch (Exception $e) {
            $pipelineData->eateries = new Collection();

            return $next($pipelineData);
        }

        /** @var EloquentCollection<int, Eatery> $ids */
        $ids = Eatery::algoliaSearchAroundLatLng($latLng, $pipelineData->searchTerm->range)->get();

        $ids = $ids
            ->reject(fn (Eatery $eatery) => $eatery->closed_down)
            ->load(['county'])
            ->reject(fn (Eatery $eatery) => $eatery->county?->county === 'Nationwide')
            ->each(function ($result): void {
                if (isset($result->scoutMetadata()['_rankingInfo']['geoDistance'])) {
                    $distance = round($result->scoutMetadata()['_rankingInfo']['geoDistance'] / 1609, 1);

                    $result->distance = $distance;
                }
            })
            ->sortBy('distance')
            ->values();

        /** @var Builder<Eatery> $query */
        $query = Eatery::query()
            ->selectDistance($latLng, ['id', 'name'])
            ->whereIn('id', $ids->pluck('id'));

        if (Arr::has($pipelineData->filters, 'categories') && $pipelineData->filters['categories'] !== null) {
            $query = $query->hasCategories($pipelineData->filters['categories']);
        }

        if (Arr::has($pipelineData->filters, 'venueTypes') && $pipelineData->filters['venueTypes'] !== null) {
            $query = $query->hasVenueTypes($pipelineData->filters['venueTypes']);
        }

        if (Arr::has($pipelineData->filters, 'features') && $pipelineData->filters['features'] !== null) {
            $query = $query->hasFeatures($pipelineData->filters['features']);
        }

        /** @var Collection<int, object{id: int, name: string, distance: null | float}> $pendingEateries */
        $pendingEateries = $query->get(['id', 'name', 'distance']);

        $pendingEateries = $pendingEateries->map(function (object $eatery) use ($ids) {
            $distance = Helpers::metersToMiles($eatery->distance ?? 0);

            if ( ! $distance) {
                /** @var Eatery $searchRecord */
                $searchRecord = $ids->firstWhere('id', $eatery->id);

                /** @var string | null $distance */
                $distance = Arr::get($searchRecord->attributesToArray(), 'distance');
            }

            return new PendingEatery(
                id: $eatery->id,
                branchId: null,
                ordering: $distance ?? $eatery->name,
                distance: (float) $distance,
            );
        });

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }

    protected function getLatLng(GetEateriesPipelineData $pipelineData): LatLng
    {
        if (LatLngState::$latLng) {
            return LatLngState::$latLng;
        }

        /** @phpstan-ignore-next-line  */
        $latLng = app(LocationSearchService::class)->getLatLng($pipelineData->searchTerm->term);

        LatLngState::$latLng = $latLng;

        return $latLng;
    }
}
