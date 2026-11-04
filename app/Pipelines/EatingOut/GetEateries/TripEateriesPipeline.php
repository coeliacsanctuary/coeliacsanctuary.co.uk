<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\TripRoute;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesAlongRouteAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesAlongRouteAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseBrowseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use App\Resources\EatingOut\EateryBrowseResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class TripEateriesPipeline
{
    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null}  $filters
     * @param  class-string<JsonResource>  $jsonResource
     * @return Collection<int, JsonResource>
     */
    public function run(TripRoute $route, float $radius, array $filters, string $jsonResource = EateryBrowseResource::class): Collection
    {
        $pipes = [
            GetEateriesAlongRouteAction::class,
            GetNationwideBranchesAlongRouteAction::class,
            SortPendingEateriesAction::class,
            SerialiseBrowseResultsAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            sort: 'distance',
            jsonResource: $jsonResource,
            route: $route,
            routeRadius: $radius,
        );

        /** @var GetEateriesPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($pipelineData)
            ->through($pipes)
            ->thenReturn();

        /** @var Collection<int, JsonResource> $serialisedEateries */
        $serialisedEateries = $pipeline->serialisedEateries;

        return $serialisedEateries;
    }
}
