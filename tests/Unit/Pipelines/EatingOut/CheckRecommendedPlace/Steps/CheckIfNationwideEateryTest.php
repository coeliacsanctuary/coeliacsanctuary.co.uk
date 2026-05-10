<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\AbstractStepAction;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\CheckIfNationwideEatery;
use Database\Seeders\EateryScaffoldingSeeder;

class CheckIfNationwideEateryTest extends StepTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsFoundAsFalseIfNothingIsFound(): void
    {
        $data = $this->makeData('Bella Italia');

        $this->getStep()->handle($data, fn () => null);

        $this->assertFalse($data->found);
    }

    #[Test]
    #[DataProvider('searchScenarios')]
    public function itReturnsAsFoundForASearchNameButNoLocation(string $name, string $search, string $branchName, string $address, string $searchAddress, string $town): void
    {
        $eatery = $this
            ->create(Eatery::class, [
                'name' => $name,
                'country_id' => $this->create(EateryCounty::class, ['county' => 'Nationwide']),
            ])
            ->load(['country', 'county', 'town']);

        $data = $this->makeData($search);

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending {$name}, did you know they're already listed on our Nationwide Eateries page?", $data->reason);
        $this->assertEquals(route('eating-out.nationwide.show', ['eatery' => $eatery->slug]), $data->url);
        $this->assertEquals("Check out {$name}", $data->label);
    }

    #[Test]
    #[DataProvider('searchScenarios')]
    public function itReturnsAsFoundForABranchSearchByTheAddress($name, $search, $branchName, $address, $searchAddress, $town): void
    {
        $eatery = $this
            ->create(Eatery::class, [
                'name' => $name,
                'country_id' => $this->create(EateryCounty::class, ['county' => 'Nationwide']),
            ])
            ->load(['country', 'county', 'town']);

        $data = $this->makeData($search, $searchAddress);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'address' => $address,
            'name' => $branchName,
        ]);

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending {$name}, {$branch->short_location}, but we've already got it listed!", $data->reason);
        $this->assertEquals($branch->link(), $data->url);
        $this->assertEquals("Check out {$name}", $data->label);
    }

    #[Test]
    #[DataProvider('searchScenarios')]
    public function itReturnsAsFoundForABranchSearchWithALocationByTheTownName($name, $search, $branchName, $address, $searchAddress, $town): void
    {
        $eatery = $this
            ->create(Eatery::class, [
                'name' => $name,
                'country_id' => $this->create(EateryCounty::class, ['county' => 'Nationwide']),
            ])
            ->load(['country', 'county', 'town']);

        $data = $this->makeData($search, $searchAddress);
        $town = $this->create(EateryTown::class, ['town' => $town]);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $eatery->id,
            'town_id' => $town->id,
            'name' => $branchName,
        ]);

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending {$name}, {$branch->short_location}, but we've already got it listed!", $data->reason);
        $this->assertEquals($branch->link(), $data->url);
        $this->assertEquals("Check out {$name}", $data->label);
    }

    public static function searchScenarios(): array
    {
        return [
            'nandos' => ["Nando's", 'nandos', '', '123 Fake Street, Crewe, AB1 2CD', 'Fake Street, Crewe, AB1 2CD', 'Crewe'],
            'nando\'s' => ["Nando's", 'nando\'s', '', '123 Fake Street, Crewe, AB1 2CD', 'Fake Street, Crewe, AB1 2CD', 'Crewe'],
            'nando' => ["Nando's", 'nando', '', '123 Fake Street, Crewe, AB1 2CD', 'Fake Street, Crewe, AB1 2CD', 'Crewe'],
            'brunning and price' => ['Brunning And Price', 'brunning and price', '', '123 Fake Street, London, AB1 2CD', 'Fake Street, London, AB1 2CD', 'London'],
            'brewers fayre' => ['Brewers Fayre', 'brewers fayre', '', '123 Fake Street, Manchester, AB1 2CD', 'Fake Street, Manchester, AB1 2CD', 'Manchester'],
            'wagamamas' => ["Wagamama's", 'wagamamas', '', 'Liverpool Dock, L1', 'Liverpool Dock, L1', 'Liverpool'],
            'wagamama\'s' => ["Wagamama's", 'wagamama\'s', '', 'Liverpool Dock, L1', 'Liverpool Dock, L1', 'Liverpool'],
            'wagamama' => ["Wagamama's", 'wagamama', '', 'Liverpool Dock, L1', 'Liverpool Dock, L1', 'Liverpool'],
        ];
    }

    protected function getStep(): AbstractStepAction
    {
        return app(CheckIfNationwideEatery::class);
    }
}
