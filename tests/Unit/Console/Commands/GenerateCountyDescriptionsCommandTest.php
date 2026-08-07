<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\OneTime\GenerateCountyDescriptionsCommand;
use App\Jobs\EatingOut\GenerateCountyDescriptionJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateCountyDescriptionsCommandTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();

        $this->build(Eatery::class)
            ->state(['county_id' => $this->county->id, 'town_id' => 1])
            ->create();
    }

    #[Test]
    public function itQueuesTheJobForACountyWithoutADescription(): void
    {
        $this->artisan(GenerateCountyDescriptionsCommand::class)->run();

        Bus::assertDispatched(GenerateCountyDescriptionJob::class);
    }

    #[Test]
    public function itDoesntQueueTheJobForACountyThatAlreadyHasADescription(): void
    {
        $this->county->updateQuietly(['description' => 'An existing description']);

        $this->artisan(GenerateCountyDescriptionsCommand::class)->run();

        Bus::assertNotDispatched(GenerateCountyDescriptionJob::class);
    }

    #[Test]
    public function itQueuesTheJobForACountyWithADescriptionWhenForced(): void
    {
        $this->county->updateQuietly(['description' => 'An existing description']);

        $this->artisan(GenerateCountyDescriptionsCommand::class, ['--force' => true])->run();

        Bus::assertDispatched(GenerateCountyDescriptionJob::class);
    }

    #[Test]
    public function itDoesntQueueTheJobForTheNationwideCounty(): void
    {
        $this->county->updateQuietly(['county' => 'Nationwide']);

        $this->artisan(GenerateCountyDescriptionsCommand::class)->run();

        Bus::assertNotDispatched(GenerateCountyDescriptionJob::class);
    }

    #[Test]
    public function itDoesntQueueTheJobForACountyWithNoActiveTowns(): void
    {
        $county = $this->build(EateryCounty::class)->create();

        $this->build(EateryTown::class)->state(['county_id' => $county->id])->create();

        $this->artisan(GenerateCountyDescriptionsCommand::class)->run();

        Bus::assertNotDispatched(
            GenerateCountyDescriptionJob::class,
            fn (GenerateCountyDescriptionJob $job) => $job->uniqueId() === $county->id
        );
    }

    #[Test]
    public function itQueuesTheJobOncePerCounty(): void
    {
        $this->build(EateryCounty::class)
            ->count(2)
            ->create()
            ->each(function (EateryCounty $county): void {
                $town = $this->build(EateryTown::class)->state(['county_id' => $county->id])->create();

                $this->build(Eatery::class)
                    ->state(['county_id' => $county->id, 'town_id' => $town->id])
                    ->create();
            });

        $this->artisan(GenerateCountyDescriptionsCommand::class)->run();

        Bus::assertDispatchedTimes(GenerateCountyDescriptionJob::class, 3);
    }
}
