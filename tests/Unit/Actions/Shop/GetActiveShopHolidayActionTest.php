<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetActiveShopHolidayAction;
use App\Models\Shop\ShopHoliday;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetActiveShopHolidayActionTest extends TestCase
{
    #[Test]
    public function itReturnsNullWhenThereArentAnyHolidays(): void
    {
        $this->assertNull($this->callAction(GetActiveShopHolidayAction::class));
    }

    #[Test]
    public function itReturnsTheHolidayWhenTodayIsInsideTheWindow(): void
    {
        $holiday = $this->create(ShopHoliday::class, [
            'start_date' => today()->subDays(2),
            'end_date' => today()->addDays(2),
        ]);

        $this->assertTrue($holiday->is($this->callAction(GetActiveShopHolidayAction::class)));
    }

    #[Test]
    public function itReturnsTheHolidayOnTheFirstDayOfTheWindow(): void
    {
        $holiday = $this->create(ShopHoliday::class, [
            'start_date' => today(),
            'end_date' => today()->addDays(2),
        ]);

        $this->assertTrue($holiday->is($this->callAction(GetActiveShopHolidayAction::class)));
    }

    #[Test]
    public function itReturnsTheHolidayOnTheLastDayOfTheWindow(): void
    {
        $holiday = $this->create(ShopHoliday::class, [
            'start_date' => today()->subDays(2),
            'end_date' => today(),
        ]);

        $this->assertTrue($holiday->is($this->callAction(GetActiveShopHolidayAction::class)));
    }

    #[Test]
    public function itReturnsNullForAHolidayThatHasntStartedYet(): void
    {
        $this->build(ShopHoliday::class)->upcoming()->create();

        $this->assertNull($this->callAction(GetActiveShopHolidayAction::class));
    }

    #[Test]
    public function itReturnsNullForAHolidayThatHasAlreadyFinished(): void
    {
        $this->build(ShopHoliday::class)->expired()->create();

        $this->assertNull($this->callAction(GetActiveShopHolidayAction::class));
    }

    #[Test]
    public function itReturnsTheFirstMatchWhenTwoHolidaysOverlap(): void
    {
        $first = $this->create(ShopHoliday::class, [
            'start_date' => today()->subDays(3),
            'end_date' => today()->addDay(),
        ]);

        $this->create(ShopHoliday::class, [
            'start_date' => today()->subDay(),
            'end_date' => today()->addDays(3),
        ]);

        $this->assertTrue($first->is($this->callAction(GetActiveShopHolidayAction::class)));
    }
}
