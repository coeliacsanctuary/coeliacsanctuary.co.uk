<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\RenderEateryMarkerAction;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\MarkerGlyphService;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RenderEateryMarkerActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    protected function renderMarker(int $typeId = EateryType::EATERY, ?int $venueTypeId = null, array $headers = []): Response
    {
        $request = Request::create('/');

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $this->callAction(RenderEateryMarkerAction::class, $request, $typeId, $venueTypeId);
    }

    #[Test]
    public function itReturnsAnSvg(): void
    {
        $response = $this->renderMarker();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('image/svg+xml', $response->headers->get('content-type'));
        $this->assertStringStartsWith('<svg', (string) $response->getContent());
    }

    #[Test]
    #[DataProvider('colourProvider')]
    public function itBakesTheEateryTypeColourIntoTheMarker(int $typeId, string $colour): void
    {
        $this->assertStringContainsString("fill=\"{$colour}\"", (string) $this->renderMarker($typeId)->getContent());
    }

    /** @return array<string, array{int, string}> */
    public static function colourProvider(): array
    {
        return [
            'eatery' => [EateryType::EATERY, '#80CCFC'],
            'attraction' => [EateryType::ATTRACTION, '#29719f'],
            'hotel' => [EateryType::HOTEL, '#DBBC25'],
            'unknown type falls back to eatery' => [99, '#80CCFC'],
        ];
    }

    #[Test]
    public function itResolvesTheGlyphForTheGivenTypeAndVenueType(): void
    {
        $venueType = $this->create(EateryVenueType::class);

        $this->mock(MarkerGlyphService::class)
            ->shouldReceive('resolve')
            ->with(EateryType::ATTRACTION, $venueType->id)
            ->once()
            ->andReturn('markers.glyphs.pub');

        $this->renderMarker(EateryType::ATTRACTION, $venueType->id);
    }

    #[Test]
    public function itIncludesTheResolvedGlyphInTheMarker(): void
    {
        $this->mock(MarkerGlyphService::class)
            ->shouldReceive('resolve')
            ->andReturn('markers.glyphs.pub');

        $this->assertStringContainsString(
            view('markers.glyphs.pub')->render(),
            (string) $this->renderMarker()->getContent(),
        );
    }

    #[Test]
    public function itStillRendersAMarkerForAVenueTypeWithoutAGlyph(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'nothing-has-this-slug']);

        $this->assertStringStartsWith('<svg', (string) $this->renderMarker(EateryType::EATERY, $venueType->id)->getContent());
    }

    #[Test]
    public function itStillRendersAMarkerForAVenueTypeThatDoesntExist(): void
    {
        $this->assertDatabaseMissing(EateryVenueType::class, ['id' => 12345]);

        $this->assertStringStartsWith('<svg', (string) $this->renderMarker(EateryType::EATERY, 12345)->getContent());
    }

    #[Test]
    public function itReturnsAPublicCacheableResponseWithAnEtag(): void
    {
        $response = $this->renderMarker();

        $this->assertNotNull($response->getEtag());
        $this->assertTrue($response->headers->hasCacheControlDirective('public'));
        $this->assertTrue($response->headers->hasCacheControlDirective('must-revalidate'));
        $this->assertEquals(3600, $response->getMaxAge());
    }

    #[Test]
    public function itReturnsA304WhenTheEtagMatches(): void
    {
        $etag = $this->renderMarker()->getEtag();

        $this->assertEquals(304, $this->renderMarker(headers: ['If-None-Match' => $etag])->getStatusCode());
    }

    #[Test]
    public function itReturnsAFreshMarkerWhenTheEtagDoesntMatch(): void
    {
        $response = $this->renderMarker(headers: ['If-None-Match' => '"not-the-right-etag"']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringStartsWith('<svg', (string) $response->getContent());
    }

    #[Test]
    public function itDoesntCacheTheMarkerLocally(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $response = $this->renderMarker();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNull($response->getEtag());
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
    }

    /** Editing a glyph or the pin has to show up straight away while developing. */
    #[Test]
    public function itNeverReturnsA304LocallyEvenWhenTheEtagWouldHaveMatched(): void
    {
        $etag = $this->renderMarker()->getEtag();

        $this->assertNotNull($etag);

        $this->app->detectEnvironment(fn () => 'local');

        $this->assertEquals(200, $this->renderMarker(headers: ['If-None-Match' => $etag])->getStatusCode());
    }
}
