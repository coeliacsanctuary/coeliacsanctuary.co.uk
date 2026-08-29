<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTownNearbyTownsTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        $this->county = $this->create(EateryCounty::class);
    }

    #[Test]
    public function itOrdersNearbyTownsByDistance(): void
    {
        $town = $this->createTownWithLiveEatery(['latlng' => '52.0,-1.0']);

        $far = $this->createTownWithLiveEatery(['latlng' => '52.5,-1.0']);
        $near = $this->createTownWithLiveEatery(['latlng' => '52.1,-1.0']);

        $nearby = $town->nearbyTowns();

        $this->assertTrue($near->is($nearby->first()));
        $this->assertTrue($far->is($nearby->last()));
    }

    #[Test]
    public function itExcludesItself(): void
    {
        $town = $this->createTownWithLiveEatery();

        $nearby = $town->nearbyTowns();

        $this->assertFalse($nearby->contains(fn (EateryTown $item) => $item->is($town)));
    }

    #[Test]
    public function itExcludesTownsInADifferentCounty(): void
    {
        $town = $this->createTownWithLiveEatery();

        $otherCounty = $this->create(EateryCounty::class);
        $townInDifferentCounty = $this->createTownWithLiveEatery(['county_id' => $otherCounty->id]);

        $nearby = $town->nearbyTowns();

        $this->assertFalse($nearby->contains(fn (EateryTown $item) => $item->is($townInDifferentCounty)));
    }

    #[Test]
    public function itExcludesTheNationwideTown(): void
    {
        $town = $this->createTownWithLiveEatery();

        $nationwide = $this->createTownWithLiveEatery(['town' => 'nationwide']);

        $nearby = $town->nearbyTowns();

        $this->assertFalse($nearby->contains(fn (EateryTown $item) => $item->is($nationwide)));
    }

    #[Test]
    public function itExcludesTownsWithoutLiveEateries(): void
    {
        $town = $this->createTownWithLiveEatery();

        $townWithOnlyABranch = $this->create(EateryTown::class, ['county_id' => $this->county->id]);
        $this->create(NationwideBranch::class, [
            'town_id' => $townWithOnlyABranch->id,
            'county_id' => $this->county->id,
        ]);

        $nearby = $town->nearbyTowns();

        $this->assertFalse($nearby->contains(fn (EateryTown $item) => $item->is($townWithOnlyABranch)));
    }

    #[Test]
    public function itRespectsTheLimit(): void
    {
        $town = $this->createTownWithLiveEatery();

        $this->createTownWithLiveEatery(['latlng' => '52.1,-1.0']);
        $this->createTownWithLiveEatery(['latlng' => '52.2,-1.0']);
        $this->createTownWithLiveEatery(['latlng' => '52.3,-1.0']);

        $nearby = $town->nearbyTowns(limit: 2);

        $this->assertCount(2, $nearby);
    }

    protected function createTownWithLiveEatery(array $attributes = []): EateryTown
    {
        $town = $this->create(EateryTown::class, array_merge([
            'county_id' => $this->county->id,
        ], $attributes));

        $this->create(Eatery::class, [
            'town_id' => $town->id,
            'county_id' => $town->county_id,
        ]);

        return $town;
    }
}
