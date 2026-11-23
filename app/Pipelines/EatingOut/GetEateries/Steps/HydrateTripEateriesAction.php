<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class HydrateTripEateriesAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        /** @var Collection<int, PendingEatery> $pendingEateries */
        $pendingEateries = $pipelineData->eateries ?? new Collection();

        $eateryIds = $pendingEateries->whereNull('branchId')->pluck('id')->unique()->values()->all();
        $branchIds = $pendingEateries->whereNotNull('branchId')->pluck('branchId')->unique()->values()->all();

        $withoutReviewScopes = fn (Builder $query): Builder => $query->withoutGlobalScopes();

        /** @var EloquentCollection<int, Eatery> $eateries */
        $eateries = Eatery::query()
            ->whereIn('id', $eateryIds)
            ->select(['id', 'name', 'town_id', 'type_id'])
            ->with('town:id,town')
            ->withCount(['reviews' => $withoutReviewScopes])
            ->withAvg(['reviews' => $withoutReviewScopes], 'rating')
            ->get();

        /** @var EloquentCollection<int, NationwideBranch> $branches */
        $branches = NationwideBranch::query()
            ->whereIn('id', $branchIds)
            ->select(['id', 'wheretoeat_id', 'name', 'town_id'])
            ->with(['town:id,town', 'eatery:id,name'])
            ->withCount(['reviews' => $withoutReviewScopes])
            ->withAvg(['reviews' => $withoutReviewScopes], 'rating')
            ->get();

        $pipelineData->hydrated = $eateries->keyBy('id');
        $pipelineData->hydratedBranches = $branches->keyBy('id');

        return $next($pipelineData);
    }
}
