<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;

abstract class BaseStep
{
    protected function pipelineDataIsValid(DetermineNationwideBranchPipelineData $data): bool
    {
        if ($data->passed) {
            return false;
        }

        if ($data->branches->isEmpty()) {
            $data->passed = true;

            return false;
        }

        return (bool) ($data->branchName);
    }

    abstract public function handle(DetermineNationwideBranchPipelineData $data, callable $next): mixed;
}
