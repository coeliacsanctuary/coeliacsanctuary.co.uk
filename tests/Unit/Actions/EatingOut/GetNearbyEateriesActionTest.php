<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetNearbyEateriesActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsAnEateryWithinTheGivenDistanceOfAnotherEatery(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $nearbyEatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(1, $result);
        $this->assertEquals($nearbyEatery->id, $result->first()['id']);
    }

    #[Test]
    public function itDoesntReturnAnEateryThatIsTooFarAway(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];
        $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $nearbyEatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $farawayEatery = $this->create(Eatery::class, [
            'lat' => $edinburgh['lat'],
            'lng' => $edinburgh['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(1, $result);
        $this->assertEquals($nearbyEatery->id, $result->first()['id']);
    }

    #[Test]
    public function itExcludesTheGivenEatery(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itReturnsABranchWithinTheGivenDistanceOfAnEatery(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $parentEatery = $this->create(Eatery::class);
        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $parentEatery->id,
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(1, $result);
        $this->assertEquals("{$parentEatery->id}-{$branch->id}", $result->first()['id']);
    }

    #[Test]
    public function itDoesntReturnABranchThatIsTooFarAwayFromTheGivenEatery(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];
        $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $parentEatery = $this->create(Eatery::class);
        $nearbyBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $parentEatery->id,
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $farAwayBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $parentEatery->id,
            'lat' => $edinburgh['lat'],
            'lng' => $edinburgh['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(1, $result);
        $this->assertEquals("{$parentEatery->id}-{$nearbyBranch->id}", $result->first()['id']);
    }

    #[Test]
    public function itReturnsABranchWithinTheGivenDistanceOfAnotherBranch(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $nearbyBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(1, $result);
        $this->assertEquals("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $result->first()['id']);
    }

    #[Test]
    public function itDoesntReturnAnBranchThatIsTooFarAway(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];
        $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $nearbyBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $farawayBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $edinburgh['lat'],
            'lng' => $edinburgh['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(1, $result);
        $this->assertEquals("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $result->first()['id']);
    }

    #[Test]
    public function itExcludesTheGivenBranch(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itReturnsAnEateryWithinTheGivenDistanceOfABranch(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(1, $result);
        $this->assertEquals($eatery->id, $result->first()['id']);
    }

    #[Test]
    public function itDoesntReturnAnEateryThatIsTooFarAwayFromTheGivenBranch(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];
        $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $farAwayEatery = $this->create(Eatery::class, [
            'lat' => $edinburgh['lat'],
            'lng' => $edinburgh['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($branch);

        $this->assertCount(1, $result);
        $this->assertEquals($eatery->id, $result->first()['id']);
    }

    #[Test]
    public function itReturnsAMixtureOfEateriesAndBranches(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $nearbyEatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $nearbyBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'] + 0.002,
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(2, $result);

        $ids = $result->pluck('id')->toArray();

        $this->assertContains($nearbyEatery->id, $ids);
        $this->assertContains("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $ids);
    }

    #[Test]
    public function itOnlyReturnsFourRecordsByDefault(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $this->build(Eatery::class)->count(5)->create([
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle($eatery);

        $this->assertCount(4, $result);
    }

    #[Test]
    public function itCanHandleARawLatLngBeingPassedInAndFindResults(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $nearbyEatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $nearbyBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'] + 0.002,
            'lng' => $london['lng'],
        ]);

        $result = app(GetNearbyEateriesAction::class)->handle(new LatLng($london['lat'], $london['lng']));;

        $this->assertCount(2, $result);

        $ids = $result->pluck('id')->toArray();

        $this->assertContains($nearbyEatery->id, $ids);
        $this->assertContains("{$nearbyBranch->wheretoeat_id}-{$nearbyBranch->id}", $ids);
    }

    #[Test]
    public function itReturnsEachResultInTheExpectedFormat(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $eatery = $this->create(Eatery::class, [
            'lat' => $london['lat'],
            'lng' => $london['lng'],
        ]);

        $nearbyEatery = $this->create(Eatery::class, [
            'lat' => $london['lat'] + 0.001,
            'lng' => $london['lng'],
        ]);

        $nearbyBranch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->build(Eatery::class),
            'lat' => $london['lat'] + 0.002,
            'lng' => $london['lng'],
        ]);

        $results = app(GetNearbyEateriesAction::class)->handle($eatery);

        $expectedKeys = ['id', 'name', 'address', 'info', 'link', 'distance', 'ratings_count', 'average_rating'];

        foreach($results as $result) {
            $this->assertArrayHasKeys($expectedKeys, $result);
        }
    }
}
