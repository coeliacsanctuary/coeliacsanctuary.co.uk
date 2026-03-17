<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class EateryQueryBuilder extends QueryBuilder
{
    protected function instantiateQuery(): Builder
    {
        return DB::table('wheretoeat')
            ->select(['wheretoeat.id', 'wheretoeat.name as ordering'])
            ->addSelect(DB::raw('null as branch_id'));
    }

    protected function additionalWhereClauses(Builder $query): void
    {
        $query
            ->where('wheretoeat.live', true)
            ->where('wheretoeat.closed_down', false);
    }
}
