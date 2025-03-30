<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\EaterySearchTerm;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToBranches;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToEateries;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesFromFiltersAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInLatLngRadiusAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInSearchAreaAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesFromFiltersAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInLatLngAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInSearchArea;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\PaginateEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\RelateEateriesAndBranchesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use App\Resources\EatingOut\EateryListResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Arr;

class GetFilteredEateriesPipeline
{
    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null }  $filters
     * @param  class-string<JsonResource>  $jsonResource
     * @return LengthAwarePaginator<JsonResource>
     */
    public function run(array $filters, string $jsonResource = EateryListResource::class): LengthAwarePaginator
    {
        $pipes = [
            GetEateriesFromFiltersAction::class,
            GetNationwideBranchesFromFiltersAction::class,
            GetEateriesInSearchAreaAction::class,
            GetNationwideBranchesInSearchArea::class,
            GetEateriesInLatLngRadiusAction::class,
            GetNationwideBranchesInLatLngAction::class,
            SortPendingEateriesAction::class,
            PaginateEateriesAction::class,
            HydrateEateriesAction::class,
            HydrateBranchesAction::class,
            AppendDistanceToEateries::class,
            AppendDistanceToBranches::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
            SerialiseResultsAction::class,
        ];

        $searchTerm = null;
        $latLng = null;

        if (Arr::has($filters, ['search', 'range']) && Arr::get($filters, 'search') !== '') {
            $searchTerm = new EaterySearchTerm();
            $searchTerm->term = Arr::getAsString($filters, 'search');
            $searchTerm->range = Arr::getAsInt($filters, 'range');
        }

        if (Arr::has($filters, ['lat', 'lng', 'range'])) {
            $latLng = new LatLng(
                lat: Arr::getAsFloat($filters, 'lat'),
                lng: Arr::getAsFloat($filters, 'lng'),
                radius: Arr::getAsInt($filters, 'range'),
            );
        }

        Arr::forget($filters, ['search', 'range', 'lat', 'lng']);

        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null } $filters */
        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            searchTerm: $searchTerm,
            jsonResource: $jsonResource,
            latLng: $latLng,
            throwSearchException: false,
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var LengthAwarePaginator<JsonResource> $serialisedEateries */
        $serialisedEateries = $pipeline->serialisedEateries;

        return $serialisedEateries;
    }
}
