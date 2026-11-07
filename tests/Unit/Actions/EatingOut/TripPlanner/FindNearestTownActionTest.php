<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\FindNearestTownAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindNearestTownActionTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        $this->county = $this->create(EateryCounty::class);
    }

    protected function townWithAPlace(string $name, ?string $latlng): void
    {
        $town = $this->create(EateryTown::class, ['town' => $name, 'latlng' => $latlng, 'county_id' => $this->county->id]);

        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $this->county->id]);
    }

    #[Test]
    public function itReturnsTheClosestTownWithPlacesAndItsCounty(): void
    {
        $this->townWithAPlace('Crewe', '53.0979,-2.4416');
        $this->townWithAPlace('Stafford', '52.8064,-2.1171');
        $this->townWithAPlace('Manchester', '53.4808,-2.2426');

        $town = $this->callAction(FindNearestTownAction::class, 52.85, -2.15);

        $this->assertSame('Stafford', $town?->town);
        $this->assertTrue($town->relationLoaded('county'));
    }

    #[Test]
    public function itIgnoresTheNationwideTownAndTownsWithoutALocation(): void
    {
        $this->townWithAPlace('nationwide', '52.85,-2.15');
        $this->townWithAPlace('Nowhere', null);
        $this->townWithAPlace('Crewe', '53.0979,-2.4416');

        $this->assertSame('Crewe', $this->callAction(FindNearestTownAction::class, 52.85, -2.15)?->town);
    }

    #[Test]
    public function itIgnoresTownsWithoutAnyPlaces(): void
    {
        $this->create(EateryTown::class, ['town' => 'Stafford', 'latlng' => '52.8064,-2.1171', 'county_id' => $this->county->id]);
        $this->townWithAPlace('Crewe', '53.0979,-2.4416');

        $this->assertSame('Crewe', $this->callAction(FindNearestTownAction::class, 52.85, -2.15)?->town);
    }

    #[Test]
    public function itReturnsNullWithoutAnyTowns(): void
    {
        $this->assertNull($this->callAction(FindNearestTownAction::class, 52.85, -2.15));
    }
}
