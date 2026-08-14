<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Filters;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class GetFiltersForNationwide extends GetFilters
{
    protected ?EateryCounty $county = null;

    protected ?array $relation = null;

    protected string $column = '';

    protected string $value = '';

    public function setCounty(EateryCounty $county): self
    {
        $this->county = $county;

        return $this;
    }

    protected function withWhereClause(Builder $builder): Builder
    {
        if ( ! $this->county) {
            throw new RuntimeException('County not set');
        }

        return $builder
            ->select('*')
            /** @phpstan-ignore argument.type */
            ->selectRaw("({$this->eateryQuery()}) as eateries_count");
    }

    protected function orderBy(Builder $builder, string $field): Builder
    {
        /** @phpstan-ignore argument.type */
        return $builder->orderByRaw("eateries_count desc, {$field} asc");
    }

    /**
     * Chains are counted once each, their nationwide branches are deliberately
     * not included, the list this filters shows one row per chain.
     */
    protected function eateryQuery(): string
    {
        return Eatery::query()
            ->selectRaw('count(*)')
            ->where('county_id', $this->county->id) /** @phpstan-ignore-line */
            ->where('live', true)
            ->when(
                $this->relation,
                fn (Builder $builder) => $builder->leftJoin(...$this->relation)->whereColumn($this->column, $this->value), /** @phpstan-ignore-line */
                fn (Builder $builder) => $builder->whereColumn($this->column, $this->value)
            )
            ->toRawSql();
    }

    protected function resolveFilters(string $filterable, string $filterName, string $orderBy, string $nameColumn, string $checkedColumn, ?callable $mergeWithMap = null): Collection
    {
        if ($filterable === EateryFeature::class) {
            $this->relation = ['wheretoeat_assigned_features', 'wheretoeat.id', 'wheretoeat_assigned_features.wheretoeat_id'];
        }

        $this->column = $this->getColumnForFilterable($filterable);
        $this->value = $this->getValueForFilterable($filterable);

        $filters = parent::resolveFilters($filterable, $filterName, $orderBy, $nameColumn, $checkedColumn, $mergeWithMap);

        $this->relation = null;
        $this->column = '';
        $this->value = '';

        return $filters;
    }

    protected function getColumnForFilterable(string $filterable): string
    {
        return match ($filterable) {
            EateryType::class => 'wheretoeat_types.id',
            EateryVenueType::class => 'wheretoeat_venue_types.id',
            EateryFeature::class => 'wheretoeat_features.id',
            default => throw new RuntimeException('Unknown filterable ' . $filterable),
        };
    }

    protected function getValueForFilterable(string $filterable): string
    {
        return match ($filterable) {
            EateryType::class => 'wheretoeat.type_id',
            EateryVenueType::class => 'wheretoeat.venue_type_id',
            EateryFeature::class => 'wheretoeat_assigned_features.feature_id',
            default => throw new RuntimeException('Unknown filterable'),
        };
    }
}
