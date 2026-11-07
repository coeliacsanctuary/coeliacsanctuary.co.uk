<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects\EatingOut;

use App\DataObjects\EatingOut\TripDestination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripDestinationTest extends TestCase
{
    protected static function encode(string $json): string
    {
        return mb_rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    #[Test]
    public function itRoundTripsThroughItsQueryValue(): void
    {
        $destination = TripDestination::fromQueryValue(new TripDestination('Dún Laoghaire, Ireland', 53.2939, -6.1339)->toQueryValue());

        $this->assertSame(['label' => 'Dún Laoghaire, Ireland', 'lat' => 53.2939, 'lng' => -6.1339], $destination?->toArray());
    }

    #[Test]
    public function itsQueryValueIsSafeToUseInAUrl(): void
    {
        $value = new TripDestination('Crewe?? ~~ Cheshire >>> ???', 53.1, -2.44)->toQueryValue();

        $this->assertDoesNotMatchRegularExpression('/[+\/=]/', $value);
    }

    #[Test]
    public function itBuildsALinkToTheTripPlanner(): void
    {
        $destination = new TripDestination('Whitby, UK', 54.4858, -0.6206);

        $this->assertSame(route('eating-out.trip-planner', ['to' => $destination->toQueryValue()]), $destination->url());
    }

    public static function invalidValues(): array
    {
        return [
            'not base64' => ['%%%not-base64%%%'],
            'not json' => [self::encode('not json')],
            'missing a label' => [self::encode('{"lat":54.48,"lng":-0.61}')],
            'empty label' => [self::encode('{"label":"","lat":54.48,"lng":-0.61}')],
            'label too long' => [self::encode('{"label":"' . str_repeat('a', 256) . '","lat":54.48,"lng":-0.61}')],
            'non numeric lat' => [self::encode('{"label":"Whitby","lat":"north","lng":-0.61}')],
            'lat out of range' => [self::encode('{"label":"Whitby","lat":154.48,"lng":-0.61}')],
            'lng out of range' => [self::encode('{"label":"Whitby","lat":54.48,"lng":-190}')],
        ];
    }

    #[Test]
    #[DataProvider('invalidValues')]
    public function itIgnoresAnInvalidQueryValue(string $value): void
    {
        $this->assertNull(TripDestination::fromQueryValue($value));
    }
}
