<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToBranches;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToEateries;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInLatLngRadiusAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesInLatLngAction;
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

class GetNearbyEateriesPipeline
{
    /**
     * @param  class-string<JsonResource>  $jsonResource
     * @return LengthAwarePaginator<int, JsonResource>
     */
    public function run(LatLng $latLng, string $jsonResource = EateryListResource::class): LengthAwarePaginator
    {
        $pipes = [
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

        $pipelineData = new GetEateriesPipelineData(
            filters: ['categories' => null, 'features' => null, 'venueTypes' => null, 'county' => null],
            jsonResource: $jsonResource,
            latLng: $latLng,
            throwSearchException: false,
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var LengthAwarePaginator<int, JsonResource> $serialisedEateries */
        $serialisedEateries = $pipeline->serialisedEateries;

        return $serialisedEateries;
    }
}
