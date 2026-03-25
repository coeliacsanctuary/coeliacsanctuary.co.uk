<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class BranchQueryBuilder extends QueryBuilder
{
    protected function getTableName(): string
    {
        return 'wheretoeat_nationwide_branches';
    }

    protected function instantiateQuery(): Builder
    {
        return DB::table('wheretoeat_nationwide_branches')
            ->select([
                'wheretoeat.id as id',
                'wheretoeat_nationwide_branches.id as branch_id',
                DB::raw('if(wheretoeat_nationwide_branches.name = "" or wheretoeat_nationwide_branches.name is null, concat(wheretoeat.name, "-", wheretoeat.id), concat(wheretoeat_nationwide_branches.name, " ", wheretoeat.name)) as ordering'),
            ]);
    }

    protected function additionalWhereClauses(Builder $query): void
    {
        $query
            ->where('wheretoeat.live', true)
            ->where('wheretoeat.closed_down', false)
            ->where('wheretoeat_nationwide_branches.live', true);
    }

    protected function prependJoins(Builder $query): void
    {
        $query->join('wheretoeat', 'wheretoeat.id', 'wheretoeat_nationwide_branches.wheretoeat_id');
    }
}
