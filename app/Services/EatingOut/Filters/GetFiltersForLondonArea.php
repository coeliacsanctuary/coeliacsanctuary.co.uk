<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Filters;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class GetFiltersForLondonArea extends GetFilters
{
    protected ?EateryArea $area = null;

    protected ?array $relation = null;

    protected string $column = '';

    protected string $value = '';

    public function setArea(EateryArea $area): self
    {
        $this->area = $area;

        return $this;
    }

    protected function withWhereClause(Builder $builder): Builder
    {
        if ( ! $this->area) {
            throw new RuntimeException('Area not set');
        }

        return $builder
            ->select('*')
            ->selectRaw("({$this->eateryQuery()}) + ({$this->branchQuery()}) as eateries_count");
    }

    protected function orderBy(Builder $builder, string $field): Builder
    {
        return $builder->orderByRaw("eateries_count desc, {$field} asc");
    }

    protected function eateryQuery(): string
    {
        return Eatery::query()
            ->selectRaw('count(*)')
            ->where('area_id', $this->area->id) /** @phpstan-ignore-line */
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
            ->where('area_id', $this->area->id) /** @phpstan-ignore-line */
            ->where('live', true)
            ->whereHas('eatery', fn (Builder $query) => $query
                ->when(
                    $this->relation,
                    fn (Builder $builder) => $builder->leftJoin(...$this->relation)->whereColumn($this->column, $this->value), /** @phpstan-ignore-line */
                    fn (Builder $builder) => $builder->whereColumn($this->column, $this->value)
                ))
            ->toRawSql();
    }

    protected function resolveFilters(string $filterable, string $filterName, string $orderBy, string $nameColumn, string $checkedColumn, ?callable $mergeWithMap = null): Collection
    {
        if ($filterable === EateryFeature::class) {
            $this->relation = ['wheretoeat_assigned_features', 'wheretoeat.id', 'wheretoeat_assigned_features.wheretoeat_id'];
        }

        $this->column = match ($filterable) {
            EateryType::class => 'wheretoeat_types.id',
            EateryVenueType::class => 'wheretoeat_venue_types.id',
            EateryFeature::class => 'wheretoeat_features.id',
            default => throw new RuntimeException('Unknown filterable ' . $filterable),
        };

        $this->value = match ($filterable) {
            EateryType::class => 'wheretoeat.type_id',
            EateryVenueType::class => 'wheretoeat.venue_type_id',
            EateryFeature::class => 'wheretoeat_assigned_features.feature_id',
            default => throw new RuntimeException('Unknown filterable'),
        };

        $filters = parent::resolveFilters($filterable, $filterName, $orderBy, $nameColumn, $checkedColumn, $mergeWithMap);

        $this->relation = null;
        $this->column = '';
        $this->value = '';

        return $filters;
    }
}
