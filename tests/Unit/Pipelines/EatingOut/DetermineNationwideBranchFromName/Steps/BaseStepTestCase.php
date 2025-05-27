<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\BaseStep;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

abstract class BaseStepTestCase extends TestCase
{
    #[Test]
    public function itDoesNothingWithTheDataIfTheDataIsAlreadyPassed(): void
    {
        $data = new DetermineNationwideBranchPipelineData(
            new Collection(),
            'foo',
            $this->create(Eatery::class),
            passed: true,
        );

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertTrue($data->passed);
            $this->assertNull($data->branch);
        };

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function itSetsPassedAsTrueIfThereAreNoBranchesSetInTheData(): void
    {
        $data = new DetermineNationwideBranchPipelineData(
            new Collection(),
            'foo',
            $this->create(Eatery::class),
            passed: false,
        );

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertTrue($data->passed);
            $this->assertNull($data->branch);
        };

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function itDoesNothingIfThereIsNoBranchNameSet(): void
    {
        $data = new DetermineNationwideBranchPipelineData(
            new Collection([$this->create(NationwideBranch::class)]),
            null,
            $this->create(Eatery::class),
            passed: false,
        );

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertFalse($data->passed);
            $this->assertNull($data->branch);
        };

        $this->factory()->handle($data, $callback);

        $data = new DetermineNationwideBranchPipelineData(
            new Collection([$this->create(NationwideBranch::class)]),
            '',
            $this->create(Eatery::class),
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    abstract protected function factory(): BaseStep;
}
