<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\NationwideBranch;
use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;

class HydrateBranchesAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ($pipelineData->paginator) {
            $items = collect($pipelineData->paginator->items());
        } elseif ($pipelineData->eateries) {
            $items = $pipelineData->eateries;
        } else {
            throw new RuntimeException('No eateries');
        }

        $branchIds = $items->reject(fn (PendingEatery $eatery) => $eatery->branchId === null)
            ->map(fn (PendingEatery $eatery) => $eatery->branchId)
            ->values()
            ->toArray();

        if (count($branchIds) === 0) {
            return $next($pipelineData);
        }

        $hydratedBranches = NationwideBranch::query()
            ->whereIn('id', $branchIds)
            ->with([
                'county', 'town', 'country',
                'reviews' => fn (Relation $builder) => $builder
                    ->select(['wheretoeat_reviews.id', 'wheretoeat_id', 'rating', 'nationwide_branch_id', 'how_expensive'])
                    ->latest('wheretoeat_reviews.created_at'),
            ])
            ->get();

        $pipelineData->hydratedBranches = $hydratedBranches;

        return $next($pipelineData);
    }
}
