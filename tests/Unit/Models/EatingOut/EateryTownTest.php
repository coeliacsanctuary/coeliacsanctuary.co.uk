<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\EateryCountry;
use PHPUnit\Framework\Attributes\Test;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EateryTownTest extends TestCase
{
    #[Test]
    public function itDispatchesTheCreateOpenGraphImageJobWhenSavedForTownAndCounty(): void
    {
        config()->set('coeliac.generate_og_images', true);
        Bus::fake();

        $county = $this->build(EateryCounty::class)->createQuietly();
        $town = $this->create(EateryTown::class, [
            'county_id' => $county->id,
        ]);

        $dispatchedModels = [];

        Bus::assertDispatched(CreateEatingOutOpenGraphImageJob::class, function (CreateEatingOutOpenGraphImageJob $job) use (&$dispatchedModels) {
            $dispatchedModels[] = $job->model;

            return true;
        });

        $this->assertCount(2, $dispatchedModels);
        $this->assertTrue($town->is($dispatchedModels[0]));
        $this->assertTrue($county->is($dispatchedModels[1]));
    }

    #[Test]
    public function itGetsALatLng(): void
    {
        $county = $this->create(EateryCounty::class, [
            'county' => 'Cheshire',
            'country_id' => $this->create(EateryCountry::class, [
                'country' => 'England',
            ]),
        ]);

        $town = $this->create(EateryTown::class, [
            'county_id' => $county->id,
            'town' => 'Crewe',
            'latlng' => null,
        ]);

        $this->assertNotNull($town->latlng);
    }
}
