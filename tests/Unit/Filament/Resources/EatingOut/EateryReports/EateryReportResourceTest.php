<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryReports;

use App\Filament\Resources\EatingOut\EateryReports\EateryReportResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReport;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryReportResourceTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itShowsTheNumberOfPendingReportsInTheNavigationBadge(): void
    {
        $this->create(EateryReport::class, 2, ['wheretoeat_id' => $this->eatery->id]);
        $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, 'completed' => true]);
        $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, 'ignored' => true]);

        $this->assertSame('2', EateryReportResource::getNavigationBadge());
    }

    #[Test]
    public function itHasNoNavigationBadgeWhenEveryReportIsCompletedOrIgnored(): void
    {
        $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, 'completed' => true]);
        $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, 'ignored' => true]);

        $this->assertNull(EateryReportResource::getNavigationBadge());
    }

    #[Test]
    public function itShowsTheNavigationBadgeInRed(): void
    {
        $this->assertSame('danger', EateryReportResource::getNavigationBadgeColor());
    }

    #[Test]
    public function itRegistersTheListPageOnly(): void
    {
        $this->assertSame(['index'], array_keys(EateryReportResource::getPages()));
    }
}
