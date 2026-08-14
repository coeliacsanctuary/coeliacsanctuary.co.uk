<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryMagicRouteRecord;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EateryAreaTest extends TestCase
{
    #[Test]
    public function itDispatchesTheCreateOpenGraphImageJobWhenSavedForAreaAndTownAndCounty(): void
    {
        config()->set('coeliac.generate_og_images', true);
        Bus::fake();

        $county = $this->build(EateryCounty::class)->createQuietly();
        $town = $this->build(EateryTown::class)->createQuietly([
            'county_id' => $county->id,
        ]);
        $area = $this->create(EateryArea::class, [
            'town_id' => $town->id,
        ]);

        $dispatchedModels = [];

        Bus::assertDispatched(CreateEatingOutOpenGraphImageJob::class, function (CreateEatingOutOpenGraphImageJob $job) use (&$dispatchedModels) {
            $dispatchedModels[] = $job->model;

            return true;
        });

        $this->assertCount(3, $dispatchedModels);
        $this->assertTrue($area->is($dispatchedModels[0]));
        $this->assertTrue($town->is($dispatchedModels[1]));
        $this->assertTrue($county->is($dispatchedModels[2]));
    }

    #[Test]
    public function itGetsALatLng(): void
    {
        $county = $this->create(EateryCounty::class, [
            'county' => 'London',
            'country_id' => $this->create(EateryCountry::class, [
                'country' => 'England',
            ]),
        ]);

        $town = $this->create(EateryTown::class, [
            'county_id' => $county->id,
            'town' => 'City of Westminster',
        ]);

        $this->create(Eatery::class, [
            'town_id' => $town->id,
            'county_id' => $county->id,
        ]);

        $area = $this->create(EateryArea::class, [
            'town_id' => $town->id,
            'area' => 'Leicester Square',
            'latlng' => null,
        ]);

        $this->assertNotNull($area->latlng);
    }

    #[Test]
    public function itHasMagicRoutes(): void
    {
        Bus::fake();

        $county = $this->create(EateryCounty::class, [
            'county' => 'London',
            'country_id' => $this->create(EateryCountry::class, [
                'country' => 'England',
            ]),
        ]);

        $town = $this->create(EateryTown::class, [
            'county_id' => $county->id,
            'town' => 'City of Westminster',
        ]);

        $this->create(Eatery::class, [
            'town_id' => $town->id,
            'county_id' => $county->id,
        ]);

        $area = $this->create(EateryArea::class, [
            'town_id' => $town->id,
            'area' => 'Leicester Square',
        ]);

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryArea::class,
            'location_id' => $area->id,
        ]);

        $this->assertCount(1, $area->magicRoutes);
        $this->assertTrue($record->is($area->magicRoutes->first()));
    }

    #[Test]
    public function itDoesntReturnMagicRoutesForOtherLocations(): void
    {
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);
        $this->create(Eatery::class);

        $town = EateryTown::query()->firstOrFail();
        $county = EateryCounty::query()->firstOrFail();

        $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryCounty::class,
            'location_id' => $county->id,
        ]);

        $otherTown = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryTown::class,
            'location_id' => $otherTown->id,
        ]);

        $this->assertCount(0, $town->magicRoutes);
    }
}
