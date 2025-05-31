<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\DetermineNationwideBranchFromName;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesName;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesTown;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\IsBranchInRequest;
use Illuminate\Pipeline\Pipeline;

class DetermineNationwideBranchFromNamePipeline
{
    public function run(Eatery $eatery, ?NationwideBranch $requestBranch, ?string $branchName): ?NationwideBranch
    {
        $eatery->loadMissing('nationwideBranches');

        $data = new DetermineNationwideBranchPipelineData(
            $eatery->nationwideBranches,
            $branchName,
            $eatery,
            $requestBranch,
        );

        $pipes = [
            IsBranchInRequest::class,
            GetBranchThatMatchesName::class,
            GetBranchThatMatchesTown::class,
        ];

        /** @var DetermineNationwideBranchPipelineData $pipeline */
        $pipeline = app(Pipeline::class)
            ->send($data)
            ->through($pipes)
            ->thenReturn();

        return $pipeline->branch;
    }
}
