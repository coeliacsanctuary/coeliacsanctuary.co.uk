<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\EatingOut;

use App\DataObjects\EatingOut\PendingEatery;
use App\Resources\EatingOut\EateryTripResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTripResourceTest extends TestCase
{
    #[Test]
    public function itAddsTheRoundedRoutePositionToTheBrowseFields(): void
    {
        $pendingEatery = new PendingEatery(
            id: 12,
            branchId: 34,
            ordering: 11119.6,
            lat: 53.1,
            lng: -2.45,
            distance: 667.7,
            typeId: 1,
            venueTypeId: 5,
            durationAlongRoute: 599.5,
        );

        $this->assertSame([
            'key' => '12-34',
            'id' => 12,
            'isNationwideBranch' => true,
            'location' => ['lat' => 53.1, 'lng' => -2.45],
            'color' => '#80CCFC',
            'typeId' => 1,
            'venueTypeId' => 5,
            'distanceAlongRoute' => 11120.0,
            'distanceFromRoute' => 668.0,
            'durationAlongRoute' => 600.0,
        ], new EateryTripResource($pendingEatery)->toArray(new Request()));
    }
}
