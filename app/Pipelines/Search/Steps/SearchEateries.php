<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use App\DataObjects\Search\SearchPipelineData;
use App\DataObjects\Search\SearchResultItem;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Search\Eateries;
use App\Services\EatingOut\LocationSearchService;
use App\Support\Helpers;
use App\Support\State\Search\SearchState;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SearchEateries
{
    public function handle(SearchPipelineData $searchPipelineData, Closure $next): mixed
    {
        if ($searchPipelineData->parameters->eateries) {
            /** @var Collection<int, Eatery|NationwideBranch> $baseResults */
            $baseResults = Eateries::search($searchPipelineData->parameters->term)
                ->with([
                    'getRankingInfo' => true,
                ])
                ->take(100)
                ->get();

            $geoResults = collect();

            $geocoder = app(LocationSearchService::class)->getLatLng(
                $searchPipelineData->parameters->locationSearch ?: $searchPipelineData->parameters->term,
                raw: true,
                nullIfEmpty: true,
                checkResult: fn ($result) => $this->geocoderIsValid($result, $searchPipelineData),
            );

            if ($geocoder) {
                $searchPipelineData->parameters->locationSearch = data_get($geocoder, 'address_components.0.long_name');

                $geoResults = $this->performGeoSearch(implode(', ', [$geocoder['lat'], $geocoder['lng']]));

                SearchState::$lat = $geocoder['lat'];
                SearchState::$lng = $geocoder['lng'];
                SearchState::$hasGeoSearched = true;
            } elseif ($searchPipelineData->parameters->userLocation) {
                $geoResults = $this->performGeoSearch(implode(',', $searchPipelineData->parameters->userLocation), $searchPipelineData->parameters->term);
            }

            $baseResults = $baseResults->map(function (Eatery|NationwideBranch $eatery) use ($geoResults) {
                /** @var Eatery|NationwideBranch|null $geoResult */
                $geoResult = $geoResults->where('id', $eatery->id)
                    ->where('slug', $eatery->slug)
                    ->first();

                if ($geoResult && $geoResult->hasAttribute('_resDistance')) {
                    $eatery->setAttribute('_resDistance', $geoResult->getAttribute('_resDistance'));
                }

                return $eatery;
            });

            $results = collect([...$geoResults->all(), ...$baseResults->all()])
                ->map(fn (Eatery|NationwideBranch $eatery) => SearchResultItem::fromSearchableResult($eatery))
                ->unique(fn (SearchResultItem $item) => "{$item->model}#{$item->id}");

            $searchPipelineData
                ->results
                ->eateries
                ->push(...$results->all());
        }

        return $next($searchPipelineData);
    }

    /** @return Collection<int, Eatery|NationwideBranch>  */
    protected function performGeoSearch(string $latLng, string $term = ''): Collection
    {
        /** @var Collection<int, Eatery|NationwideBranch> $geoResults */
        $geoResults = Eateries::search($term)
            ->with([
                'getRankingInfo' => true,
                'aroundLatLng' => $latLng,
                'aroundRadius' => Helpers::milesToMeters(5),
            ])
            ->take(100)
            ->get();

        return $geoResults->map(function (Eatery|NationwideBranch $eatery): Eatery|NationwideBranch {
            $eatery->setAttribute('_resDistance', Arr::get($eatery->scoutMetadata(), '_rankingInfo.geoDistance', 0));

            return $eatery;
        });
    }

    protected function geocoderIsValid(?array $geocoder, SearchPipelineData $searchPipelineData): bool
    {
        if ( ! $geocoder) {
            return false;
        }

        if ($searchPipelineData->parameters->locationSearch) {
            return false;
        }

        if ( ! Arr::has($geocoder, 'address_components')) {
            return false;
        }

        return data_get($geocoder, 'address_components.0.types.0') !== 'country';
    }
}
