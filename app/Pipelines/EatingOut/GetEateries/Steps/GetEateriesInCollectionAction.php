<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Services\EatingOut\Collection\Builder\EateryQueryBuilder;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GetEateriesInCollectionAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->configuration) {
            throw new RuntimeException('No configuration');
        }

        /** @var Collection<int, object{id: int, branch_id: int | null, ordering: string}> $pendingEateries */
        $pendingEateries = collect(DB::select(new EateryQueryBuilder($pipelineData->configuration)->toSql()));

        $pendingEateries = $pendingEateries->map(fn (object $eatery) => new PendingEatery(
            id: $eatery->id,
            branchId: $eatery->branch_id,
            ordering: $eatery->ordering,
        ));

        if ( ! $pipelineData->eateries instanceof Collection) {
            $pipelineData->eateries = new Collection();
        }

        $pipelineData->eateries->push(...$pendingEateries);

        return $next($pipelineData);
    }
}
