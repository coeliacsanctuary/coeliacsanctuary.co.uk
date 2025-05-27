<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\DetermineNationwideBranchFromName;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesName;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\GetBranchThatMatchesTown;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\Steps\IsBranchInRequest;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[Test]
    #[DataProvider('branchNames')]
    public function itCanResolveTheCorrectNationwideBranch(callable $setup, string $searchString): void
    {
        [$eatery, $expectedBranch] = $setup($this);

        $branch = app(DetermineNationwideBranchFromNamePipeline::class)->run($eatery, null, $searchString);

        $this->assertNotNull($branch);
        $this->assertTrue($branch->is($expectedBranch));
    }

    public static function branchNames(): array
    {
        return [
            'exact match' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);
                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => 'Foo Bar',
                    ]);

                    return [$eatery, $branch];
                },
                'foo bar',
            ],
            'partial match' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);
                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => 'Foo Bar',
                    ]);

                    return [$eatery, $branch];
                },
                'foo',
            ],
            'town match' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $town = $test->create(EateryTown::class, [
                        'town' => 'Crewe',
                    ]);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => null,
                        'town_id' => $town->id,
                    ]);

                    return [$eatery, $branch];
                },
                'crewe',
            ],
            "'the' prefix matching" => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => 'Superclub',
                    ]);

                    return [$eatery, $branch];
                },
                'the superclub',
            ],
            "'the' prefix missing" => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => 'The Superclub',
                    ]);

                    return [$eatery, $branch];
                },
                'superclub',
            ],
            'branch name and town together' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $town = $test->create(EateryTown::class, [
                        'town' => 'Crewe',
                    ]);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => 'The Superclub',
                        'town_id' => $town->id,
                    ]);

                    return [$eatery, $branch];
                },
                'The Superclub, Crewe',
            ],
            'street and town in search term' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $town = $test->create(EateryTown::class, [
                        'town' => 'Crewe',
                    ]);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => null,
                        'town_id' => $town->id,
                        'address' => "My Street,\nCrewe",
                    ]);

                    return [$eatery, $branch];
                },
                'My Street, Crewe',
            ],
            'street and town in search term without comma' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $town = $test->create(EateryTown::class, [
                        'town' => 'Crewe',
                    ]);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => null,
                        'town_id' => $town->id,
                        'address' => "My Street,\nCrewe",
                    ]);

                    return [$eatery, $branch];
                },
                'Crewe Highstreet',
            ],
            'town in brackets with place name' => [
                function (self $test) {
                    $eatery = $test->create(Eatery::class);

                    $town = $test->create(EateryTown::class, [
                        'town' => 'Crewe',
                    ]);

                    $branch = $test->create(NationwideBranch::class, [
                        'wheretoeat_id' => $eatery->id,
                        'name' => 'Superclub',
                        'town_id' => $town->id,
                    ]);

                    return [$eatery, $branch];
                },
                'Superclub (Crewe)',
            ],
        ];
    }
}
