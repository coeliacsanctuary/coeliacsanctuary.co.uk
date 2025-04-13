<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Models\EatingOut\EaterySearchTerm;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToBranches;
use App\Pipelines\EatingOut\GetEateries\Steps\AppendDistanceToEateries;
use App\Pipelines\EatingOut\GetEateries\Steps\CheckForMissingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesInSearchAreaAction;
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

class GetSearchResultsPipeline
{
    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null }  $filters
     * @param  class-string<JsonResource>  $jsonResource
     * @return LengthAwarePaginator<int, JsonResource>
     */
    public function run(EaterySearchTerm $eaterySearchTerm, array $filters, string $jsonResource = EateryListResource::class): LengthAwarePaginator
    {
        $pipes = [
            GetEateriesInSearchAreaAction::class,
            GetNationwideBranchesInSearchArea::class,
            SortPendingEateriesAction::class,
            PaginateEateriesAction::class,
            HydrateEateriesAction::class,
            AppendDistanceToEateries::class,
            HydrateBranchesAction::class,
            AppendDistanceToBranches::class,
            CheckForMissingEateriesAction::class,
            RelateEateriesAndBranchesAction::class,
            SerialiseResultsAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            searchTerm: $eaterySearchTerm,
            jsonResource: $jsonResource
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
