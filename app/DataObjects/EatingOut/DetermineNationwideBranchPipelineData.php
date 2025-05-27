<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Collection;

class DetermineNationwideBranchPipelineData
{
    /**
     * @param  Collection<int, NationwideBranch>  $branches
     */
    public function __construct(
        public Collection $branches,
        public ?string $branchName,
        public Eatery $eatery,
        public ?NationwideBranch $requestBranch = null,
        public ?NationwideBranch $branch = null,
        public bool $passed = false,
    ) {
    }
}
