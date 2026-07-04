<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\NationwideBranchResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class GetGroupedEateryNationwideBranchesAction
{
    /**
     * @param  Collection<int, NationwideBranch>  $branches
     * @param  class-string<JsonResource>  $formatter
     */
    public function handle(Collection $branches, string $formatter = NationwideBranchResource::class): array
    {
        return $branches
            ->groupBy(fn (NationwideBranch $branch) => $branch->country->country) /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(fn (Collection $branches) => $this->groupByCounty($branches, $formatter))
            ->toArray();
    }

    /**
     * @param  Collection<int, NationwideBranch>  $branches
     * @param  class-string<JsonResource>  $formatter
     * @return Collection<int|string, Collection<int|string, Collection<int|string, Collection<int, JsonResource>>>>
     */
    protected function groupByCounty(Collection $branches, string $formatter): Collection
    {
        return $branches
            ->groupBy(fn (NationwideBranch $branch) => $branch->county->county) /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(fn (Collection $branches) => $this->groupByTown($branches, $formatter));
    }

    /**
     * @param  Collection<int, NationwideBranch>  $branches
     * @param  class-string<JsonResource>  $formatter
     * @return Collection<int|string, Collection<int|string, Collection<int, JsonResource>>>
     */
    protected function groupByTown(Collection $branches, string $formatter): Collection
    {
        return $branches
            ->groupBy(fn (NationwideBranch $branch) => $branch->town->town) /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(fn (Collection $branches) => $this->groupByArea($branches, $formatter));
    }

    /**
     * @param  Collection<int, NationwideBranch>  $branches
     * @param  class-string<JsonResource>  $formatter
     * @return Collection<int|string, Collection<int, JsonResource>>
     */
    protected function groupByArea(Collection $branches, string $formatter): Collection
    {
        return $branches
            ->groupBy(fn (NationwideBranch $branch) => $branch->area?->area ?? '_') /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(fn (Collection $branches) => $branches
                ->sortBy('name')
                ->values()
                ->map(fn (NationwideBranch $branch) => $formatter::make($branch)));
    }
}
