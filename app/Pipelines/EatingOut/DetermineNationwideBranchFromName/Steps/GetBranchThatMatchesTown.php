<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GetBranchThatMatchesTown extends BaseStep
{
    public function handle(DetermineNationwideBranchPipelineData $data, callable $next): mixed
    {
        if ($this->pipelineDataIsValid($data) === false) {
            return $next($data);
        }

        $town = EateryTown::withoutGlobalScopes()
            ->whereIn('id', $data->branches->pluck('town_id')->unique()->toArray())
            ->where(fn(Builder $builder) => $builder
                ->whereLike('town', "%{$data->branchName}%")
                ->orWhere(function (Builder $builder) use ($data): void {
                    Str::of($data->branchName)
                        ->explode(' ')
                        ->map(fn (string $term) => mb_trim($term, " \n\r\t\v\0,"))
                        ->each(fn (string $term) => $builder->orWhereLike('town', "%{$term}%"));
                })
            )
            ->first();

        if ($town) {
            $matchingBranches = $data->branches->filter(fn (NationwideBranch $branch) => $branch->town_id === $town->id);

            if ($matchingBranches->count() === 1) {
                $data->branch = $matchingBranches->first();
                $data->passed = true;
            }
        }

        return $next($data);
    }
}
