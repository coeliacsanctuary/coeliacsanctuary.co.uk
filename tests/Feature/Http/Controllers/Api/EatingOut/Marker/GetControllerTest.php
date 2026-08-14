<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\Marker;

use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\MarkerGlyphService;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    protected function visitRoute(int $typeId = EateryType::EATERY, ?int $venueTypeId = null, array $headers = []): TestResponse
    {
        return $this->get(
            route('api.wheretoeat.marker.get', array_filter([
                'typeId' => $typeId,
                'venueTypeId' => $venueTypeId,
            ])),
            $headers,
        );
    }

    #[Test]
    public function itReturnsAnSvg(): void
    {
        $response = $this->visitRoute();

        $response->assertOk();
        $response->assertHeader('content-type', 'image/svg+xml');

        $this->assertStringContainsString('<svg', $response->getContent());
    }

    #[Test]
    public function itBakesTheEateryTypeColourIntoTheMarker(): void
    {
        $this->assertStringContainsString('#DBBC25', $this->visitRoute(EateryType::HOTEL)->getContent());
        $this->assertStringContainsString('#29719f', $this->visitRoute(EateryType::ATTRACTION)->getContent());
    }

    #[Test]
    public function itResolvesTheGlyphForTheGivenTypeAndVenueType(): void
    {
        $venueType = $this->create(EateryVenueType::class);

        $this->mock(MarkerGlyphService::class)
            ->shouldReceive('resolve')
            ->withArgs(function (int $typeId, ?int $venueTypeId) use ($venueType) {
                $this->assertEquals(EateryType::ATTRACTION, $typeId);
                $this->assertEquals($venueType->id, $venueTypeId);

                return true;
            })
            ->once()
            ->andReturn('markers.glyphs.generic.attraction');

        $this->visitRoute(EateryType::ATTRACTION, $venueType->id)->assertOk();
    }

    #[Test]
    public function itReturnsAnEtagAndA304WhenItMatches(): void
    {
        $response = $this->visitRoute();

        $etag = $response->headers->get('etag');

        $this->assertNotNull($etag);

        $this->visitRoute(headers: ['If-None-Match' => $etag])->assertStatus(304);
    }

    #[Test]
    public function itReturnsAFreshMarkerWhenTheEtagDoesntMatch(): void
    {
        $this->visitRoute(headers: ['If-None-Match' => '"not-the-right-etag"'])->assertOk();
    }

    #[Test]
    public function itDoesntCacheTheMarkerLocally(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $response = $this->visitRoute();

        $response->assertOk();

        $this->assertNull($response->headers->get('etag'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('cache-control'));
    }

    /** Editing a glyph or the pin has to show up straight away while developing. */
    #[Test]
    public function itNeverReturnsA304LocallyEvenWhenTheEtagWouldHaveMatched(): void
    {
        $etag = $this->visitRoute()->headers->get('etag');

        $this->assertNotNull($etag);

        $this->app->detectEnvironment(fn () => 'local');

        $this->visitRoute(headers: ['If-None-Match' => $etag])->assertOk();
    }

    #[Test]
    public function itFallsBackToAGenericGlyphForAVenueTypeWithoutOne(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'nothing-has-this-slug']);

        $this->visitRoute(EateryType::EATERY, $venueType->id)
            ->assertOk()
            ->assertHeader('content-type', 'image/svg+xml');
    }

    #[Test]
    public function itStillReturnsAMarkerWithNoVenueType(): void
    {
        $this->visitRoute(EateryType::EATERY)->assertOk();
    }

    #[Test]
    public function itStillReturnsAMarkerForAVenueTypeThatDoesntExist(): void
    {
        $this->assertDatabaseMissing(EateryVenueType::class, ['id' => 12345]);

        $this->visitRoute(EateryType::EATERY, 12345)->assertOk();
    }

    #[Test]
    public function itReturnsANotFoundForNonNumericIds(): void
    {
        $this->get('/api/wheretoeat/marker/foo')->assertNotFound();
    }
}
