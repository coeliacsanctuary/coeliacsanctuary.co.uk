<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\DetermineNationwideBranchFromName;

use App\Models\EatingOut\Eatery;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesName;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesTown;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\IsBranchInRequest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DetermineNationwideBranchFromNamePipelineTest extends TestCase
{
    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(IsBranchInRequest::class);
        $this->expectPipelineToExecute(GetBranchThatMatchesName::class);
        $this->expectPipelineToExecute(GetBranchThatMatchesTown::class);

        $this->runPipeline(DetermineNationwideBranchFromNamePipeline::class, $this->create(Eatery::class), null, 'foo');
    }
}
