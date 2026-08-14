<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\AbstractStepAction;
use App\Pipelines\EatingOut\CheckRecommendedPlace\Steps\CheckIfStandardEatery;
use Database\Seeders\EateryScaffoldingSeeder;

class CheckIfStandardEateryTest extends StepTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsNotFoundIfThereIsNoLocation(): void
    {
        $this->create(Eatery::class, ['name' => 'Cafe']);

        $data = $this->makeData('Cafe');

        $this->getStep()->handle($data, fn () => null);

        $this->assertFalse($data->found);
    }

    #[Test]
    public function itReturnsFoundAsFalseIfNothingIsFoundForTheNameAndLocation(): void
    {
        $data = $this->makeData('Doesnt Exist', 'Anywhere');

        $this->getStep()->handle($data, fn () => null);

        $this->assertFalse($data->found);
    }

    #[Test]
    public function itReturnsFoundAsFalseIfNothingIsFoundForTheLocation(): void
    {
        $this->create(Eatery::class, ['name' => 'Foo']);

        $data = $this->makeData('Foo', 'Bar');

        $this->getStep()->handle($data, fn () => null);

        $this->assertFalse($data->found);
    }

    #[Test]
    #[DataProvider('searchScenarios')]
    public function itReturnsAsFoundForAEateryByTheNameAndAddress($name, $search, $address, $searchAddress, $town): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $eatery = $this
            ->create(Eatery::class, [
                'name' => $name,
                'county_id' => $county->id,
                'town_id' => $town->id,
                'address' => $address,
            ])
            ->load(['country', 'county', 'town']);

        $data = $this->makeData($search, $searchAddress);

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending {$eatery->name}, {$eatery->short_location}, did you know they're already listed in our eating out guide?", $data->reason);
        $this->assertEquals($eatery->link(), $data->url);
        $this->assertEquals("Check out {$name}", $data->label);
    }

    #[Test]
    #[DataProvider('searchScenarios')]
    public function itReturnsAsFoundForAnEaterySearchWithANameAndLocationByTheTownName($name, $search, $address, $searchAddress, $town): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id, 'town' => $town]);

        $data = $this->makeData($search, $searchAddress);

        $eatery = $this
            ->create(Eatery::class, [
                'name' => $name,
                'county_id' => $county->id,
                'town_id' => $town->id,
            ])
            ->load(['country', 'county', 'town']);

        $this->getStep()->handle($data, fn () => null);

        $this->assertTrue($data->found);
        $this->assertEquals("It looks like you're recommending {$eatery->name}, {$eatery->short_location}, did you know they're already listed in our eating out guide?", $data->reason);
        $this->assertEquals($eatery->link(), $data->url);
        $this->assertEquals("Check out {$name}", $data->label);
    }

    public static function searchScenarios(): array
    {
        return [
            'red lion' => ['The Red Lion', 'red lion', '123 Fake Street, Crewe, AB1 2CD', 'Fake Street, Crewe, AB1 2CD', 'Crewe'],
            'surf cafe' => ['Surf Cafe', 'surf ca', '123 Fake Street, Stoke, AB1 2CD', 'Fake Street, Stoke, AB1 2CD', 'Stoke'],
            'golden fleece' => ['The Golden Fleece', 'the golden fleece', '123 Fake Street, Birmingham, AB1 2CD', 'Fake Street, Birmingham, AB1 2CD', 'Birmingham'],
        ];
    }

    protected function getStep(): AbstractStepAction
    {
        return app(CheckIfStandardEatery::class);
    }
}
