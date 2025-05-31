<?php

declare(strict_types=1);

namespace App\Services\EatingOut\Filters;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class GetFiltersForSearchResults extends GetFiltersForTown
{
    protected ?string $searchKey = null;

    public function handle(array $filters = []): array
    {
        $result = parent::handle($filters);

        Cache::forget("search-filters-{$this->searchKey}");

        return $result;
    }

    public function usingSearchKey(string $searchKey): self
    {
        $this->searchKey = $searchKey;

        return $this;
    }

    protected function withWhereClause(Builder $builder): Builder
    {
        if ( ! $this->searchKey) {
            throw new RuntimeException('Search Key Not Set');
        }

        return $builder
            ->select('*')
            ->selectRaw("({$this->eateryQuery()}) + ({$this->branchQuery()}) as eateries_count");
    }

    protected function getExposedSearchResults(): array
    {
        return once(fn () => Cache::get("search-filters-{$this->searchKey}", []));
    }

    protected function eateryQuery(): string
    {
        return Eatery::query()
            ->selectRaw('count(*)')
            ->whereIn('id', Arr::get($this->getExposedSearchResults(), 'eateryIds', []))
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
            ->whereIn('id', Arr::get($this->getExposedSearchResults(), 'branchIds', []))
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
