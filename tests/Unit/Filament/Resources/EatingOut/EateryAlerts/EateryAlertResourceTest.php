<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAlerts;

use App\Filament\Resources\EatingOut\EateryAlerts\EateryAlertResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryAlertResourceTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itShowsTheNumberOfPendingAlertsInTheNavigationBadge(): void
    {
        $this->build(EateryAlert::class)->on($this->eatery)->count(2)->create();
        $this->build(EateryAlert::class)->on($this->eatery)->completed()->create();
        $this->build(EateryAlert::class)->on($this->eatery)->ignored()->create();

        $this->assertSame('2', EateryAlertResource::getNavigationBadge());
    }

    #[Test]
    public function itHasNoNavigationBadgeWhenEveryAlertIsCompletedOrIgnored(): void
    {
        $this->build(EateryAlert::class)->on($this->eatery)->completed()->create();
        $this->build(EateryAlert::class)->on($this->eatery)->ignored()->create();

        $this->assertNull(EateryAlertResource::getNavigationBadge());
    }

    #[Test]
    public function itShowsTheNavigationBadgeInRed(): void
    {
        $this->assertSame('danger', EateryAlertResource::getNavigationBadgeColor());
    }

    #[Test]
    public function itRegistersTheListPageOnly(): void
    {
        $this->assertSame(['index'], array_keys(EateryAlertResource::getPages()));
    }
}
