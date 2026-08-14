<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Filters;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Support\State\EatingOut\Search\SearchResultIdsState;
use Illuminate\Database\Eloquent\Builder;

class GetFiltersForSearchResults extends GetFiltersForTown
{
    protected function withWhereClause(Builder $builder): Builder
    {
        return $builder
            ->select('*')
            /** @phpstan-ignore argument.type */
            ->selectRaw("({$this->eateryQuery()}) + ({$this->branchQuery()}) as eateries_count");
    }

    protected function eateryQuery(): string
    {
        return Eatery::query()
            ->selectRaw('count(*)')
            ->whereIn('id', SearchResultIdsState::$eateryIds)
            ->where('live', true)
            ->when(
                $this->relation,
                fn (Builder $builder) => $builder->leftJoin(...$this->relation)->whereColumn($this->column, $this->value), /** @phpstan-ignore-line */
                fn (Builder $builder) => $builder->whereColumn($this->column, $this->value)
            )
            ->toRawSql();
    }

    protected function branchQuery(): string
    {
        return NationwideBranch::query()
            ->selectRaw('count(*)')
            ->whereIn('id', SearchResultIdsState::$branchIds)
            ->where('live', true)
            ->whereHas('eatery', fn (Builder $query) => $query
                ->when(
                    $this->relation,
                    fn (Builder $builder) => $builder->leftJoin(...$this->relation)->whereColumn($this->column, $this->value), /** @phpstan-ignore-line */
                    fn (Builder $builder) => $builder->whereColumn($this->column, $this->value)
                ))
            ->toRawSql();
    }
}
