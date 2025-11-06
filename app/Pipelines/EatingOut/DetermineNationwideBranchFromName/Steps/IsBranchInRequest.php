<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;

class IsBranchInRequest extends BaseStep
{
    public function handle(DetermineNationwideBranchPipelineData $data, callable $next): mixed
    {
        if ($this->pipelineDataIsValid($data) === false) {
            return $next($data);
        }

        dd('here');

        if ($data->requestBranch?->wheretoeat_id === $data->eatery->id) {
            $data->branch = $data->requestBranch;
            $data->passed = true;
        }

        return $next($data);
    }
}
