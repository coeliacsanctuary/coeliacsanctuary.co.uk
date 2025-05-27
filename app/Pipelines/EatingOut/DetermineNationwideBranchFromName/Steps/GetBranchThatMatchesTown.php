<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;

class GetBranchThatMatchesTown extends BaseStep
{
    public function handle(DetermineNationwideBranchPipelineData $data, callable $next): mixed
    {
        if ($this->pipelineDataIsValid($data) === false) {
            return $next($data);
        }

        $town = EateryTown::withoutGlobalScopes()
            ->whereLike('town', "%{$data->branchName}%")
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
