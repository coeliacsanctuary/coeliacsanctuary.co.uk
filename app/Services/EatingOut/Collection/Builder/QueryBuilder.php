<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Collection\Builder;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Average;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Count;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Order;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QueryBuilder
{
    public function __construct(protected Configuration $configuration)
    {
        //
    }

    public function toSql(): string
    {
        $query = DB::table('wheretoeat')->select('wheretoeat.id');

        if ($this->configuration->getCounts()->isNotEmpty()) {
            $this->applyCountQueries($this->configuration->getCounts(), $query);
        }

        if ($this->configuration->getAverages()->isNotEmpty()) {
            $this->applyAverageQueries($this->configuration->getAverages(), $query);
        }

        if ($this->configuration->getJoins()->isNotEmpty()) {
            $this->configuration->getJoins()->each(fn ($join) => $join($query));
        }

        if ($this->configuration->getWheres()->isNotEmpty()) {
            $this->applyWhereClauses($this->configuration->getWheres(), $query);
        }

        $query
            ->where('wheretoeat.live', true)
            ->where('wheretoeat.closed_down', false);

        if ($this->configuration->getOrderings()->isNotEmpty()) {
            $this->processOrderClauses($this->configuration->getOrderings(), $query);
        }

        if ($this->configuration->getLimit()) {
            $query->limit($this->configuration->getLimit());
        }

        return $query->toRawSql();
    }

    /** @param Collection<int, Count> $counts */
    protected function applyCountQueries(Collection $counts, Builder $query): Builder
    {
        $counts->each(function (Count $count) use ($query): void {
            $query
                ->addSelect(
                    DB::raw("(select count(*) from {$count->table} where {$count->localKey} = {$count->foreignKey}) as {$count->alias}")
                )
                ->having($count->alias, $count->operator, $count->value);
        });

        return $query;
    }

    /** @param Collection<int, Average> $averages */
    protected function applyAverageQueries(Collection $averages, Builder $query): Builder
    {
        $averages->each(function (Average $average) use ($query): void {
            $query
                ->addSelect(
                    DB::raw("(select avg({$average->column}) from {$average->table} where {$average->localKey} = {$average->foreignKey}) as {$average->alias}")
                )
                ->having($average->alias, $average->operator, $average->value);
        });

        return $query;
    }

    /** @param Collection<int, Where|Collection<int, Where>> $clauses */
    protected function applyWhereClauses(Collection $clauses, Builder $query): Builder
    {
        $query->where(function (Builder $builder) use ($clauses): void {
            $clauses->each(function (Where|Collection $where) use ($builder): void {
                if ($where instanceof Where) {
                    $where($builder);

                    return;
                }

                $this->applyWhereClauses($where, $builder);
            });
        });

        return $query;
    }

    protected function processOrderClauses(Collection $getOrderings, Builder $query): void
    {
        $getOrderings->each(function (Order $order) use ($query): void {
            if ($order->table && $order->localKey && $order->foreignKey) {
                $query->leftJoin($order->table, $order->localKey, '=', $order->foreignKey);
            }

            $query->orderBy($order->column, $order->direction);
        });
    }
}
