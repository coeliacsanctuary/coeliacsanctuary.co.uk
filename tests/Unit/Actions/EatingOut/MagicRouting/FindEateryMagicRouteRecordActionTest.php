<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\MagicRouting;

use App\Actions\EatingOut\MagicRouting\FindEateryMagicRouteRecordAction;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\EateryMagicRouteRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindEateryMagicRouteRecordActionTest extends TestCase
{
    #[Test]
    public function itReturnsAnExistingRecordIfOneExists(): void
    {
        $record = $this->create(EateryMagicRouteRecord::class, [
            'resolver_type' => EateryMagicRouteType::HundredPercentGlutenFree,
            'raw_location' => 'test-location',
        ]);

        $result = $this->callAction(FindEateryMagicRouteRecordAction::class, EateryMagicRouteType::HundredPercentGlutenFree, 'test-location');

        $this->assertSame($record->id, $result->id);
    }

    #[Test]
    public function itThrowsIfNoRecordExists(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->callAction(FindEateryMagicRouteRecordAction::class, EateryMagicRouteType::HundredPercentGlutenFree, 'test-location');
    }
}
