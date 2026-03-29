<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\GetEateries\Steps;

use App\Contracts\EatingOut\GetEateriesPipelineActionContract;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Services\EatingOut\Collection\Builder\EateryQueryBuilder;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use Closure;
use Illuminate\Support\Arr;
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

        if (Arr::has($pipelineData->filters, 'categories') && $pipelineData->filters['categories'] !== null) {
            $wheres = [];

            foreach ($pipelineData->filters['categories'] as $category) {
                $wheres[] = new Where('wheretoeat_types.type', '=', $category, 'or');
            }

            $pipelineData->configuration->addWhere($wheres);
            $pipelineData->configuration->addJoin(new Join('wheretoeat_types', 'wheretoeat_types.id', '=', 'wheretoeat.type_id'));
        }

        if (Arr::has($pipelineData->filters, 'venueTypes') && $pipelineData->filters['venueTypes'] !== null) {
            $wheres = [];

            foreach ($pipelineData->filters['venueTypes'] as $venueType) {
                $wheres[] = new Where('wheretoeat_venue_types.slug', '=', $venueType, 'or');
            }

            $pipelineData->configuration->addWhere($wheres);
            $pipelineData->configuration->addJoin(new Join('wheretoeat_venue_types', 'wheretoeat_venue_types.id', '=', 'wheretoeat.venue_type_id'));
        }

        if (Arr::has($pipelineData->filters, 'features') && $pipelineData->filters['features'] !== null) {
            $wheres = [];

            foreach ($pipelineData->filters['features'] as $feature) {
                $wheres[] = new Where('wheretoeat_features.slug', '=', $feature, 'or');
            }

            $pipelineData->configuration->addWhere($wheres);
            $pipelineData->configuration->addJoin(new Join('wheretoeat_assigned_features', 'wheretoeat_assigned_features.wheretoeat_id', '=', 'wheretoeat.id'));
            $pipelineData->configuration->addJoin(new Join('wheretoeat_features', 'wheretoeat_features.id', '=', 'wheretoeat_assigned_features.feature_id'));
        }

        /** @phpstan-ignore-next-line  */
        if (Arr::has($pipelineData->filters, 'towns') && $pipelineData->filters['towns'] !== null) {
            $wheres = [];

            foreach ($pipelineData->filters['towns'] as $town) {
                $wheres[] = new Where('wheretoeat.town_id', '=', $town, 'or');
            }

            $pipelineData->configuration->addWhere($wheres);
        }

        /** @phpstan-ignore-next-line  */
        if (Arr::has($pipelineData->filters, 'counties') && $pipelineData->filters['counties'] !== null) {
            $wheres = [];

            foreach ($pipelineData->filters['counties'] as $county) {
                $wheres[] = new Where('wheretoeat.county_id', '=', $county, 'or');
            }

            $pipelineData->configuration->addWhere($wheres);
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
