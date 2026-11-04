<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\DecodePolylineAction;
use App\DataObjects\EatingOut\LatLng;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DecodePolylineActionTest extends TestCase
{
    #[Test]
    public function itDecodesAnEncodedPolylineIntoLatLngs(): void
    {
        $points = $this->callAction(DecodePolylineAction::class, '_p~iF~ps|U_ulLnnqC_mqNvxq`@');

        $this->assertSame(
            [[38.5, -120.2], [40.7, -120.95], [43.252, -126.453]],
            $points->map(fn (LatLng $point): array => [$point->lat, $point->lng])->all(),
        );
    }

    #[Test]
    public function itReturnsAnEmptyCollectionForAnEmptyString(): void
    {
        $this->assertCount(0, $this->callAction(DecodePolylineAction::class, ''));
    }
}
