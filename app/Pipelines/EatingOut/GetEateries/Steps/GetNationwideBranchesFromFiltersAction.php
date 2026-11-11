<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Models\EatingOut\EaterySearchTerm;
use App\DataObjects\EatingOut\LatLng;
use App\Builders\EatingOut\EateryBuilder;
use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\NationwideBranch;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class GetNationwideBranchesFromFiltersAction implements GetEateriesPipelineActionContract
{
    public function handle(GetEateriesPipelineData $pipelineData, Closure $next): mixed
    {
        if ($pipelineData->searchTerm instanceof EaterySearchTerm || $pipelineData->latLng instanceof LatLng) {
            return $next($pipelineData);
        }

        $query = NationwideBranch::query()
            /** @lang mysql */
            /** @phpstan-ignore argument.type */
            ->selectRaw(Arr::join([
                'wheretoeat.id as id',
                'wheretoeat_nationwide_branches.id as branch_id',
                'if(wheretoeat_nationwide_branches.name = "" or wheretoeat_nationwide_branches.name is null, concat(wheretoeat.name, "-", wheretoeat.id), concat(wheretoeat_nationwide_branches.name, " ", wheretoeat.name)) as ordering',
            ], ','))
            ->join('wheretoeat', 'wheretoeat.id', 'wheretoeat_nationwide_branches.wheretoeat_id')
            ->whereHas('eatery', function (EateryBuilder $query) use ($pipelineData): EateryBuilder {
                $query->where('closed_down', false);
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

        $pendingEateries = $pendingEateries->map(fn (object $eatery): PendingEatery => new PendingEatery(
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
