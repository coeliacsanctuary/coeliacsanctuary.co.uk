<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps;

use App\DataObjects\EatingOut\DetermineNationwideBranchPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\BaseStep;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesName;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class GetBranchThatMatchesNameTest extends BaseStepTestCase
{
    #[Test]
    #[DataProvider('branchNames')]
    public function ifThereIsABranchWithAMatchingNameInTheGivenBranchesItWillUseThat($branchName, $searchString): void
    {
        $eatery = $this->create(Eatery::class);

        $branches = $this->build(NationwideBranch::class)
            ->forEatery($eatery)
            ->count(2)
            ->sequence(
                [
                    'name' => $branchName,
                ],
                [
                    'name' => 'foo bar baz',
                ],
            )
            ->create();

        $callback = function (DetermineNationwideBranchPipelineData $data) use ($branches): void {
            $this->assertTrue($data->passed);
            $this->assertNotNull($data->branch);
            $this->assertTrue($data->branch->is($branches->first()));
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection($branches),
            $searchString,
            $eatery,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function ifThereIsMoreThanOneBranchWithAMatchingNameInTheGivenBranchesItWillNotUseThat(): void
    {
        $eatery = $this->create(Eatery::class);

        $branches = $this->build(NationwideBranch::class)
            ->forEatery($eatery)
            ->count(2)
            ->sequence(
                [
                    'name' => 'My Branch On',
                ],
                [
                    'name' => 'My Branch On My Street',
                ],
            )
            ->create();

        $callback = function (DetermineNationwideBranchPipelineData $data): void {
            $this->assertFalse($data->passed);
            $this->assertNull($data->branch);
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection($branches),
            'My Branch',
            $eatery,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    #[Test]
    public function ifThereIsMoreThanOneBranchWithAMatchingNameInTheGivenBranchesButThereIsAnExactMatchItWillUseThat(): void
    {
        $eatery = $this->create(Eatery::class);

        $branches = $this->build(NationwideBranch::class)
            ->forEatery($eatery)
            ->count(2)
            ->sequence(
                [
                    'name' => 'My Branch',
                ],
                [
                    'name' => 'My Branch On My Street',
                ],
            )
            ->create();

        $callback = function (DetermineNationwideBranchPipelineData $data) use ($branches): void {
            $this->assertTrue($data->passed);
            $this->assertNotNull($data->branch);
            $this->assertTrue($data->branch->is($branches->first()));
        };

        $data = new DetermineNationwideBranchPipelineData(
            new Collection($branches),
            'My Branch',
            $eatery,
            passed: false,
        );

        $this->factory()->handle($data, $callback);
    }

    public static function branchNames(): array
    {
        return [
            'exact match' => ['my branch', 'my branch'],
            'partial match' => ['my branch', 'branch'],
            'mixed case match' => ['My Branch', 'my branch'],
            "'the' prefix" => ['branch', 'the branch'],
            "'the' prefix missing" => ['the branch', 'branch'],
        ];
    }

    protected function factory(): BaseStep
    {
        return new GetBranchThatMatchesName();
    }
}
