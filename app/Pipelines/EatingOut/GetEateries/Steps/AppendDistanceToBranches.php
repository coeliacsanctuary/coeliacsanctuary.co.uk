<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\NationwideBranch;
use Closure;
use Illuminate\Support\Collection;

class AppendDistanceToBranches implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, PendingEatery> $results */
        $results = $pipelineData->eateries;

        /** @var Collection<int, NationwideBranch> | null $hydratedBranches */
        $hydratedBranches = $pipelineData->hydratedBranches;

        if ($hydratedBranches) {
            $pipelineData->hydratedBranches = $hydratedBranches->map(function (NationwideBranch $branch) use ($results): NationwideBranch {
                $rawData = $results->firstWhere('branchId', $branch->id);

                if ($rawData && $rawData->distance) {
                    $branch->distance = $rawData->distance;
                }

                return $branch;
            });
        }

        return $next($pipelineData);
    }
}
