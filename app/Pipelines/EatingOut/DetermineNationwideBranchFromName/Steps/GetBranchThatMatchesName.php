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

        $branchName = $data->branchName;

        if (Str::startsWith($branchName, 'the ')) {
            $branchName = Str::replaceFirst('the ', '', $branchName);
        }

        $matchingBranches = $data->branches
            ->reject(fn (NationwideBranch $branch) => $branch->name === null)
            /** @phpstan-ignore-next-line */
            ->filter(fn (NationwideBranch $branch) => Str::of($branch->name)->contains($branchName, true) || Str::of($branchName)->contains($branch->name, false));

        if ($matchingBranches->count() === 1) {
            $data->branch = $matchingBranches->first();
            $data->passed = true;
        }

        return $next($data);
    }
}
