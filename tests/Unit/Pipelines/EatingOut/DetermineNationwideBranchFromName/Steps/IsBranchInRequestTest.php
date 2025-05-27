<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\BaseStep;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\IsBranchInRequest;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;

class IsBranchInRequestTest extends BaseStepTestCase
{
    #[Test]
    public function itUsesAGivenBranchIfItIsAChildOfTheGivenEatery(): void
    {
        $eatery = $this->create(Eatery::class);
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
        ]);

        $callback = function (DetermineNationwideBranchPipelineData $data) use ($branch): void {
            $this->assertTrue($data->passed);
            $this->assertNotNull($data->branch);
            $this->assertTrue($data->branch->is($branch));
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection([$branch]),
            'foo',
            $eatery,
            $branch,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function itDoesntUseAGivenBranchIfItIsNotAChildOfTheGivenEatery(): void
    {
        $eatery = $this->create(Eatery::class);
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
        ]);

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertFalse($data->passed);
            $this->assertNull($data->branch);
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection([$branch]),
            'foo',
            $eatery,
            $branch,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    protected function factory(): BaseStep
    {
        return new IsBranchInRequest();
    }
}
