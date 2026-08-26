<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Shop;

use App\Models\Shop\ShopHoliday;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopHolidayTest extends TestCase
{
    #[Test]
    public function itBuildsTheNoticeFromTheThreeDates(): void
    {
        $holiday = $this->create(ShopHoliday::class, [
            'start_date' => '2026-04-03',
            'end_date' => '2026-04-06',
            'ship_on' => '2026-04-06',
        ]);

        $this->assertEquals(
            'The shop is on holiday from Friday 3rd April until Monday 6th April. Any orders placed during this time will be despatched on Monday 6th April.',
            $holiday->notice,
        );
    }

    #[Test]
    public function itUsesTheShipOnDateWhenItFallsAfterTheHoliday(): void
    {
        $holiday = $this->create(ShopHoliday::class, [
            'start_date' => '2026-04-03',
            'end_date' => '2026-04-13',
            'ship_on' => '2026-04-14',
        ]);

        $this->assertStringContainsString('despatched on Tuesday 14th April.', $holiday->notice);
    }

    #[Test]
    public function itCastsTheDatesToDateInstances(): void
    {
        $holiday = $this->create(ShopHoliday::class);

        $this->assertTrue($holiday->start_date->isStartOfDay());
        $this->assertTrue($holiday->end_date->isStartOfDay());
        $this->assertTrue($holiday->ship_on->isStartOfDay());
    }
}
