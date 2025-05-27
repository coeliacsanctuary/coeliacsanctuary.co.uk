<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\BaseStep;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesTown;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;

class GetBranchThatMatchesTownTest extends BaseStepTestCase
{
    #[Test]
    public function ifTheSearchStringDoesntMatchATownNameThenNothingIsSet(): void
    {
        $this->create(EateryTown::class, [
            'town' => 'foo',
        ]);

        $eatery = $this->create(Eatery::class);

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertFalse($data->passed);
            $this->assertNull($data->branch);
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection([$this->create(NationwideBranch::class)]),
            'bar',
            $eatery,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function ifTheSearchStringDoesMatchATownNameButThereAreNoBranchesInThatTownThenNothingIsSet(): void
    {
        $this->create(EateryTown::class, [
            'town' => 'foo',
        ]);

        $eatery = $this->create(Eatery::class);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'town_id' => $this->create(EateryTown::class)->id,
        ]);

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertFalse($data->passed);
            $this->assertNull($data->branch);
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection([$branch]),
            'foo',
            $eatery,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function ifTheSearchStringDoesMatchATownNameAndThereIsABranchInThatTownThenItWillUseThat(): void
    {
        $town = $this->create(EateryTown::class, [
            'town' => 'foo',
        ]);

        $eatery = $this->create(Eatery::class);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'town_id' => $town->id,
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
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function ifTheSearchStringDoesMatchATownNameButThereIsMoreThatOneBranchInThatTownThenItWillNotSetAnything(): void
    {
        $town = $this->create(EateryTown::class, [
            'town' => 'foo',
        ]);

        $eatery = $this->create(Eatery::class);

        $branches = $this->create(NationwideBranch::class, 2, [
            'wheretoeat_id' => $eatery->id,
            'town_id' => $town->id,
        ]);

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertFalse($data->passed);
            $this->assertNull($data->branch);
        };

        $data = new DetermineNationwideBranchPipelineData(
            $branches,
            'foo',
            $eatery,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    protected function factory(): BaseStep
    {
        return new GetBranchThatMatchesTown();
    }
}
