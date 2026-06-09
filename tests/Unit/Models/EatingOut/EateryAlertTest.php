<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryAlertTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itHasAnEateryRelationship(): void
    {
        $eatery = $this->create(Eatery::class);

        $alert = $this->build(EateryAlert::class)->on($eatery)->create();

        $this->assertInstanceOf(Eatery::class, $alert->eatery);
        $this->assertTrue($eatery->is($alert->eatery));
    }

    #[Test]
    public function itCastsCompletedToBoolean(): void
    {
        $eatery = $this->create(Eatery::class);

        $alert = $this->build(EateryAlert::class)->on($eatery)->completed()->create();

        $this->assertIsBool($alert->completed);
        $this->assertTrue($alert->completed);
    }

    #[Test]
    public function itCastsIgnoredToBoolean(): void
    {
        $eatery = $this->create(Eatery::class);

        $alert = $this->build(EateryAlert::class)->on($eatery)->ignored()->create();

        $this->assertIsBool($alert->ignored);
        $this->assertTrue($alert->ignored);
    }

    #[Test]
    public function itHasAPendingScope(): void
    {
        $eatery = $this->create(Eatery::class);

        $pendingAlert = $this->build(EateryAlert::class)->on($eatery)->create();
        $completedAlert = $this->build(EateryAlert::class)->on($eatery)->completed()->create();
        $ignoredAlert = $this->build(EateryAlert::class)->on($eatery)->ignored()->create();

        $pendingAlerts = EateryAlert::query()->pending()->get();

        $this->assertCount(1, $pendingAlerts);
        $this->assertTrue($pendingAlert->is($pendingAlerts->first()));
    }
}
