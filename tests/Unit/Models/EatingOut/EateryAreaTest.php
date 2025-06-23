<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\EateryArea;
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
}
