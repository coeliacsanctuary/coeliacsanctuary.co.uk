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
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;

class GetNationwideBranchesInTownAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ( ! $pipelineData->town) {
            throw new RuntimeException('No Town');
        }

        /** @var Builder<Eatery> $query */
        $query = NationwideBranch::query()
            /** @lang mysql */
            ->selectRaw(Arr::join([
                'wheretoeat.id as id',
                'wheretoeat_nationwide_branches.id as branch_id',
                'if(wheretoeat_nationwide_branches.name = "", concat(wheretoeat.name, "-", wheretoeat.id), concat(wheretoeat_nationwide_branches.name, " ", wheretoeat.name)) as ordering',
            ], ','))
            ->where('wheretoeat_nationwide_branches.town_id', $pipelineData->town->id)
            ->join('wheretoeat', 'wheretoeat.id', 'wheretoeat_nationwide_branches.wheretoeat_id')
            ->whereHas('eatery', function (Builder $query) use ($pipelineData) {
                /** @var Builder<Eatery> $query */
                $query->where('closed_down', false); /** @phpstan-ignore-line */
                if (Arr::has($pipelineData->filters, 'categories') && $pipelineData->filters['categories'] !== null) {
                    $query = $query->hasCategories($pipelineData->filters['categories']);
                }

                if (Arr::has($pipelineData->filters, 'venueTypes') && $pipelineData->filters['venueTypes'] !== null) {
                    $query = $query->hasVenueTypes($pipelineData->filters['venueTypes']);
                }

                if (Arr::has($pipelineData->filters, 'features') && $pipelineData->filters['features'] !== null) {
                    $query = $query->hasFeatures($pipelineData->filters['features']);
                }

                return $query;
            })
            ->orderBy('ordering');

        /** @var Collection<int, object{id: int, branch_id: int | null, ordering: string}> $pendingEateries */
        $pendingEateries = $query->toBase()->get();

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
