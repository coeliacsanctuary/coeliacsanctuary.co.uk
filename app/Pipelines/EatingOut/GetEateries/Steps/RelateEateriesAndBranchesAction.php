<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Closure;
use Illuminate\Support\Collection;
use RuntimeException;

class RelateEateriesAndBranchesAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        $hydratedBranches = $pipelineData->hydratedBranches;

        if ( ! $hydratedBranches || $hydratedBranches->count() === 0) {
            return $next($pipelineData);
        }

        if ($pipelineData->paginator) {
            $pendingEateries = collect($pipelineData->paginator->items());
        } elseif ($pipelineData->eateries) {
            $pendingEateries = $pipelineData->eateries;
        } else {
            throw new RuntimeException('No eateries');
        }

        /** @var Collection<int, Eatery> $hydratedEateries */
        $hydratedEateries = $pipelineData->hydrated;

        $pipelineData->hydrated = $hydratedEateries->map(function (Eatery $eatery, $index) use ($hydratedBranches, $pendingEateries) {
            /** @var PendingEatery $currentPendingEatery */
            $currentPendingEatery = $pendingEateries->at($index);

            if ($currentPendingEatery->branchId === null) {
                return $eatery;
            }

            /** @var NationwideBranch $currentBranch */
            $currentBranch = $hydratedBranches->firstWhere('id', $currentPendingEatery->branchId);

            /** @phpstan-ignore-next-line  */
            $currentBranch->reviews = $currentBranch->reviews->merge($eatery->reviews)->unique('id')->sortByDesc('created_at');

            $eatery->setRelation('branch', $currentBranch);
            $currentBranch->setRelation('eatery', $eatery);

            return $eatery;
        });

        return $next($pipelineData);
    }
}
