<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Str;

class GetBranchThatMatchesName extends BaseStep
{
    public function handle(DetermineNationwideBranchPipelineData $data, callable $next): mixed
    {
        if ($this->pipelineDataIsValid($data) === false) {
            return $next($data);
        }

        $matchingBranches = $data->branches
            ->reject(fn (NationwideBranch $branch) => $branch->name === null)
            /** @phpstan-ignore-next-line */
            ->filter(fn (NationwideBranch $branch) => Str::of($branch->name)->contains($data->branchName, true));

        if ($matchingBranches->count() === 1) {
            $data->branch = $matchingBranches->first();
            $data->passed = true;
        }

        return $next($data);
    }
}
