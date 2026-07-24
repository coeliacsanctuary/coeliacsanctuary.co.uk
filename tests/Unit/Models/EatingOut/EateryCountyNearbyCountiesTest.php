<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCountyNearbyCountiesTest extends TestCase
{
    protected EateryCountry $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = $this->create(EateryCountry::class);
    }

    #[Test]
    public function itOrdersNearbyCountiesByDistance(): void
    {
        $county = $this->createCountyWithActiveTown(['latlng' => '52.0,-1.0']);

        $far = $this->createCountyWithActiveTown(['latlng' => '52.5,-1.0']);
        $near = $this->createCountyWithActiveTown(['latlng' => '52.1,-1.0']);

        $nearby = $county->nearbyCounties();

        $this->assertTrue($near->is($nearby->first()));
        $this->assertTrue($far->is($nearby->last()));
    }

    #[Test]
    public function itExcludesItself(): void
    {
        $county = $this->createCountyWithActiveTown();

        $nearby = $county->nearbyCounties();

        $this->assertFalse($nearby->contains(fn (EateryCounty $item) => $item->is($county)));
    }

    #[Test]
    public function itExcludesCountiesInADifferentCountry(): void
    {
        $county = $this->createCountyWithActiveTown();

        $otherCountry = $this->create(EateryCountry::class);
        $otherCountyInDifferentCountry = $this->createCountyWithActiveTown(['country_id' => $otherCountry->id]);

        $nearby = $county->nearbyCounties();

        $this->assertFalse($nearby->contains(fn (EateryCounty $item) => $item->is($otherCountyInDifferentCountry)));
    }

    #[Test]
    public function itExcludesTheNationwideCounty(): void
    {
        $county = $this->createCountyWithActiveTown();

        $nationwide = $this->createCountyWithActiveTown(['county' => 'Nationwide']);

        $nearby = $county->nearbyCounties();

        $this->assertFalse($nearby->contains(fn (EateryCounty $item) => $item->is($nationwide)));
    }

    #[Test]
    public function itExcludesCountiesWithoutActiveTowns(): void
    {
        $county = $this->createCountyWithActiveTown();

        $countyWithoutActiveTown = $this->create(EateryCounty::class, [
            'country_id' => $this->country->id,
        ]);

        $nearby = $county->nearbyCounties();

        $this->assertFalse($nearby->contains(fn (EateryCounty $item) => $item->is($countyWithoutActiveTown)));
    }

    #[Test]
    public function itRespectsTheLimit(): void
    {
        $county = $this->createCountyWithActiveTown();

        $this->createCountyWithActiveTown(['latlng' => '52.1,-1.0']);
        $this->createCountyWithActiveTown(['latlng' => '52.2,-1.0']);
        $this->createCountyWithActiveTown(['latlng' => '52.3,-1.0']);

        $nearby = $county->nearbyCounties(limit: 2);

        $this->assertCount(2, $nearby);
    }

    protected function createCountyWithActiveTown(array $attributes = []): EateryCounty
    {
        $county = $this->create(EateryCounty::class, array_merge([
            'country_id' => $this->country->id,
        ], $attributes));

        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $this->create(Eatery::class, [
            'town_id' => $town->id,
            'county_id' => $county->id,
        ]);

        return $county;
    }
}
