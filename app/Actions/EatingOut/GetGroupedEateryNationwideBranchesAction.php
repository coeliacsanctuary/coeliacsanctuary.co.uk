<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\NationwideBranchResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class GetGroupedEateryNationwideBranchesAction
{
    /**
     * @param  Collection<int, NationwideBranch> | Eatery  $branches  An eatery loads its own live branches first.
     * @param  class-string<JsonResource>  $formatter
     */
    public function handle(Collection|Eatery $branches, string $formatter = NationwideBranchResource::class): array
    {
        if ($branches instanceof Eatery) {
            $branches = $this->loadBranchesFor($branches);
        }

        return $branches
            ->groupBy(fn (NationwideBranch $branch) => $branch->country->country) /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(fn (Collection $branches) => $this->groupByCounty($branches, $formatter))
            ->toArray();
    }

    /**
     * Eager loads everything the grouping and the resource reach for, so that
     * neither this action nor NationwideBranchResource triggers an N+1.
     *
     * @return Collection<int, NationwideBranch>
     */
    protected function loadBranchesFor(Eatery $eatery): Collection
    {
        $eatery->loadMissing([
            'nationwideBranches.eatery', 'nationwideBranches.area', 'nationwideBranches.area.town', 'nationwideBranches.town',
            'nationwideBranches.town.county', 'nationwideBranches.county', 'nationwideBranches.country',
        ]);

        /** @var Collection<int, NationwideBranch> $branches */
        $branches = $eatery->nationwideBranches;

        return $branches;
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
