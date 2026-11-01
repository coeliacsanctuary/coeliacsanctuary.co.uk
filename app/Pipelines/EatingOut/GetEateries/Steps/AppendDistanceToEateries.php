<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use Closure;
use Illuminate\Support\Collection;

class AppendDistanceToEateries implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, PendingEatery> $results */
        $results = $pipelineData->eateries;

        /** @var Collection<int, Eatery> $hydratedEateries */
        $hydratedEateries = $pipelineData->hydrated;

        $pipelineData->hydrated = $hydratedEateries->map(function (Eatery $eatery) use ($results): Eatery {
            $rawData = $results->firstWhere('id', $eatery->id);

            if ($rawData && $rawData->distance) {
                $eatery->distance = $rawData->distance;
            }

            return $eatery;
        });

        return $next($pipelineData);
    }
}
