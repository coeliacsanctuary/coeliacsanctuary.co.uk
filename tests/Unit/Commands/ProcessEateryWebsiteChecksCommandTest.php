<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use App\Jobs\EatingOut\CheckSingleEateryWebsiteJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use App\Models\EatingOut\EateryCheck;
use App\Models\EatingOut\EateryCounty;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProcessEateryWebsiteChecksCommandTest extends TestCase
{
    protected EateryCounty $regularCounty;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        // Create a non-nationwide county (id != 1) for regular eateries
        $this->regularCounty = $this->build(EateryCounty::class)->create([
            'id' => 2,
            'county' => 'Regular County',
            'country_id' => 1,
        ]);
    }

    #[Test]
    public function itDispatchesJobsForEateriesWithWebsites(): void
    {
        $eateryWithWebsite = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $eateryWithoutWebsite = $this->create(Eatery::class, [
            'website' => null,
            'county_id' => $this->regularCounty->id,
        ]);

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eateryWithWebsite) {
            $this->assertTrue($job->eatery->is($eateryWithWebsite));

            return true;
        });

        Bus::assertNotDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eateryWithoutWebsite) {
            $this->assertFalse($job->eatery->is($eateryWithoutWebsite));

            return false;
        });
    }

    #[Test]
    public function itExcludesClosedDownEateries(): void
    {
        $openEatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'closed_down' => false,
            'county_id' => $this->regularCounty->id,
        ]);

        $closedEatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'closed_down' => true,
            'county_id' => $this->regularCounty->id,
        ]);

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($openEatery) {
            $this->assertTrue($job->eatery->is($openEatery));

            return true;
        });

        Bus::assertNotDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($closedEatery) {
            $this->assertFalse($job->eatery->is($closedEatery));

            return false;
        });
    }

    #[Test]
    public function itExcludesNationwideEateries(): void
    {
        $regularEatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $nationwideEatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => 1, // county_id = 1 is nationwide
        ]);

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($regularEatery) {
            $this->assertTrue($job->eatery->is($regularEatery));

            return true;
        });

        Bus::assertNotDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($nationwideEatery) {
            $this->assertFalse($job->eatery->is($nationwideEatery));

            return false;
        });
    }

    #[Test]
    public function itExcludesEateriesWithPendingAlerts(): void
    {
        $eateryWithoutAlert = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $eateryWithPendingAlert = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->build(EateryAlert::class)->on($eateryWithPendingAlert)->websiteAlert()->create();

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eateryWithoutAlert) {
            $this->assertTrue($job->eatery->is($eateryWithoutAlert));

            return true;
        });

        Bus::assertNotDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eateryWithPendingAlert) {
            $this->assertFalse($job->eatery->is($eateryWithPendingAlert));

            return false;
        });
    }

    #[Test]
    public function itIncludesEateriesWithCompletedAlerts(): void
    {
        $eatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->build(EateryAlert::class)->on($eatery)->websiteAlert()->completed()->create();

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eatery) {
            $this->assertTrue($job->eatery->is($eatery));

            return true;
        });
    }

    #[Test]
    public function itIncludesEateriesWithIgnoredAlerts(): void
    {
        $eatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->build(EateryAlert::class)->on($eatery)->websiteAlert()->ignored()->create();

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eatery) {
            $this->assertTrue($job->eatery->is($eatery));

            return true;
        });
    }

    #[Test]
    public function itExcludesRecentlyCheckedEateries(): void
    {
        $recentlyChecked = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->build(EateryCheck::class)->create([
            'wheretoeat_id' => $recentlyChecked->id,
            'website_checked_at' => now()->subDays(15),
        ]);

        $notRecentlyChecked = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->build(EateryCheck::class)->create([
            'wheretoeat_id' => $notRecentlyChecked->id,
            'website_checked_at' => now()->subDays(45),
        ]);

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($notRecentlyChecked) {
            $this->assertTrue($job->eatery->is($notRecentlyChecked));

            return true;
        });

        Bus::assertNotDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($recentlyChecked) {
            $this->assertFalse($job->eatery->is($recentlyChecked));

            return false;
        });
    }

    #[Test]
    public function itIncludesNeverCheckedEateries(): void
    {
        $neverChecked = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($neverChecked) {
            $this->assertTrue($job->eatery->is($neverChecked));

            return true;
        });
    }

    #[Test]
    public function itIncludesEateriesWithCheckRecordButNullWebsiteCheckedAt(): void
    {
        $eatery = $this->create(Eatery::class, [
            'website' => 'https://example.com',
            'county_id' => $this->regularCounty->id,
        ]);

        $this->build(EateryCheck::class)->create([
            'wheretoeat_id' => $eatery->id,
            'website_checked_at' => null,
            'google_checked_at' => now(),
        ]);

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatched(CheckSingleEateryWebsiteJob::class, function ($job) use ($eatery) {
            $this->assertTrue($job->eatery->is($eatery));

            return true;
        });
    }

    #[Test]
    public function itLimitsBatchSize(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $this->create(Eatery::class, [
                'website' => 'https://example.com',
                'county_id' => $this->regularCounty->id,
            ]);
        }

        $this->artisan('coeliac:process-eatery-website-checks');

        Bus::assertDispatchedTimes(CheckSingleEateryWebsiteJob::class, 150);
    }
}
