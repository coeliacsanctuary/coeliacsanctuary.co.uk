<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut;

use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\MarkerGlyphService;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Contracts\View\Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MarkerGlyphServiceTest extends TestCase
{
    protected MarkerGlyphService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->service = app(MarkerGlyphService::class);
    }

    #[Test]
    public function itResolvesTheGlyphForAVenueTypeThatHasOne(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'pub']);

        $this->assertEquals('markers.glyphs.pub', $this->service->resolve(EateryType::EATERY, $venueType->id));
    }

    #[Test]
    public function itPrefersTheVenueTypeGlyphOverTheGenericOne(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'hotel']);

        $this->assertEquals('markers.glyphs.hotel', $this->service->resolve(EateryType::HOTEL, $venueType->id));
    }

    #[Test]
    public function itFallsBackToTheGenericGlyphForAVenueTypeWithNoGlyphOfItsOwn(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'nothing-has-this-slug']);

        $this->assertEquals('markers.glyphs.generic.eatery', $this->service->resolve(EateryType::EATERY, $venueType->id));
    }

    #[Test]
    public function itFallsBackToTheGenericGlyphWhenThereIsNoVenueType(): void
    {
        $this->assertEquals('markers.glyphs.generic.eatery', $this->service->resolve(EateryType::EATERY));
    }

    #[Test]
    public function itFallsBackToTheGenericGlyphForAVenueTypeThatDoesntExist(): void
    {
        $this->assertDatabaseMissing(EateryVenueType::class, ['id' => 12345]);

        $this->assertEquals('markers.glyphs.generic.eatery', $this->service->resolve(EateryType::EATERY, 12345));
    }

    #[Test]
    #[DataProvider('eateryTypeProvider')]
    public function itResolvesTheGenericGlyphForEachEateryType(int $typeId, string $expected): void
    {
        $this->assertEquals("markers.glyphs.generic.{$expected}", $this->service->resolve($typeId));
    }

    /** @return array<string, array{int, string}> */
    public static function eateryTypeProvider(): array
    {
        return [
            'eatery' => [EateryType::EATERY, 'eatery'],
            'attraction' => [EateryType::ATTRACTION, 'attraction'],
            'hotel' => [EateryType::HOTEL, 'hotel'],
        ];
    }

    #[Test]
    public function itFallsBackToAnEateryGlyphForAnEateryTypeItDoesntKnowAbout(): void
    {
        $this->assertEquals('markers.glyphs.generic.eatery', $this->service->resolve(99));
    }

    /**
     * Adding a venue type is a data change made in Nova, with no code alongside
     * it, so nothing else would catch a glyph that resolves to a view which
     * isn't there.
     */
    #[Test]
    public function everyVenueTypeResolvesToAGlyphThatExists(): void
    {
        $viewFactory = app(Factory::class);

        $venueTypes = EateryVenueType::query()->get();

        $this->assertNotEmpty($venueTypes);

        foreach ($venueTypes as $venueType) {
            foreach ([EateryType::EATERY, EateryType::ATTRACTION, EateryType::HOTEL] as $typeId) {
                $glyph = $this->service->resolve($typeId, $venueType->id);

                $this->assertTrue(
                    $viewFactory->exists($glyph),
                    "Venue type [{$venueType->slug}] resolved to [{$glyph}], which does not exist",
                );
            }
        }
    }
}
