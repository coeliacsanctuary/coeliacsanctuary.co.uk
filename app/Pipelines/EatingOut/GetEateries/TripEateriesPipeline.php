<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\TripRoute;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesAlongRouteAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesAlongRouteAction;
use App\Pipelines\EatingOut\GetEateries\Steps\HydrateTripEateriesAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseTripResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

class TripEateriesPipeline
{
    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null}  $filters
     * @return Collection<int, JsonResource>
     */
    public function run(TripRoute $route, float $radius, array $filters): Collection
    {
        $pipes = [
            GetEateriesAlongRouteAction::class,
            GetNationwideBranchesAlongRouteAction::class,
            SortPendingEateriesAction::class,
            HydrateTripEateriesAction::class,
            SerialiseTripResultsAction::class,
        ];

        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            sort: 'distance',
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
