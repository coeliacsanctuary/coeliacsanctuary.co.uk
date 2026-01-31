<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\CreateEateryAlertAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateEateryAlertActionTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itCreatesAnAlert(): void
    {
        $this->assertCount(0, $this->eatery->alerts);

        $alert = $this->callAction(CreateEateryAlertAction::class, $this->eatery, 'website', 'Test details');

        $this->assertInstanceOf(EateryAlert::class, $alert);
        $this->assertCount(1, $this->eatery->refresh()->alerts);
        $this->assertEquals('website', $alert->type);
        $this->assertEquals('Test details', $alert->details);
        $this->assertFalse($alert->completed);
        $this->assertFalse($alert->ignored);
    }

    #[Test]
    public function itSkipsDuplicatePendingAlerts(): void
    {
        $this->build(EateryAlert::class)->on($this->eatery)->websiteAlert()->create();

        $result = $this->callAction(CreateEateryAlertAction::class, $this->eatery, 'website', 'Test details');

        $this->assertNull($result);
        $this->assertCount(1, $this->eatery->refresh()->alerts);
    }

    #[Test]
    public function itCreatesAlertWhenExistingAlertIsCompleted(): void
    {
        $this->build(EateryAlert::class)->on($this->eatery)->websiteAlert()->completed()->create();

        $alert = $this->callAction(CreateEateryAlertAction::class, $this->eatery, 'website', 'Test details');

        $this->assertInstanceOf(EateryAlert::class, $alert);
        $this->assertCount(2, $this->eatery->refresh()->alerts);
    }

    #[Test]
    public function itCreatesAlertWhenExistingAlertIsIgnored(): void
    {
        $this->build(EateryAlert::class)->on($this->eatery)->websiteAlert()->ignored()->create();

        $alert = $this->callAction(CreateEateryAlertAction::class, $this->eatery, 'website', 'Test details');

        $this->assertInstanceOf(EateryAlert::class, $alert);
        $this->assertCount(2, $this->eatery->refresh()->alerts);
    }

    #[Test]
    public function itCreatesAlertForDifferentType(): void
    {
        $this->build(EateryAlert::class)->on($this->eatery)->websiteAlert()->create();

        $alert = $this->callAction(CreateEateryAlertAction::class, $this->eatery, 'google_places', 'Test details');

        $this->assertInstanceOf(EateryAlert::class, $alert);
        $this->assertCount(2, $this->eatery->refresh()->alerts);
        $this->assertEquals('google_places', $alert->type);
    }
}
