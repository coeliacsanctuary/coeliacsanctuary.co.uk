<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Resources\EatingOut\EateryTripResource;
use Closure;
use Illuminate\Support\Collection;

class SerialiseTripResultsAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, PendingEatery> $eateries */
        $eateries = $pipelineData->eateries ?? new Collection();

        $pipelineData->serialisedEateries = $eateries
            ->map(fn (PendingEatery $eatery): EateryTripResource => new EateryTripResource(
                $eatery,
                $eatery->branchId
                    ? $pipelineData->hydratedBranches?->get($eatery->branchId)
                    : $pipelineData->hydrated?->get($eatery->id),
            ))
            ->values();

        return $next($pipelineData);
    }
}
