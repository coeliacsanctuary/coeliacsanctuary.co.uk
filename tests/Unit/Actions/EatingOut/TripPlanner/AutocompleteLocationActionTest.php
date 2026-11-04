<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\AutocompleteLocationAction;
use App\Models\OpenRouteServiceAutocompleteCache;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AutocompleteLocationActionTest extends TestCase
{
    protected function fakeAutocompleteResponse(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'api.openrouteservice.org/geocode/autocomplete*' => Http::response([
                'features' => [
                    ['properties' => ['id' => '1', 'label' => 'Ennis, CE, Ireland'], 'geometry' => ['coordinates' => [-8.98, 52.84]]],
                    ['properties' => ['id' => '2', 'label' => 'Ennis, CE, Ireland'], 'geometry' => ['coordinates' => [-8.99, 52.85]]],
                    ['properties' => ['id' => '3', 'label' => 'Enniskillen, Northern Ireland, United Kingdom'], 'geometry' => ['coordinates' => [-7.63, 54.34]]],
                ],
            ]),
        ]);
    }

    #[Test]
    public function itReturnsNothingForATermUnderThreeCharactersWithoutCallingTheApi(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $this->assertSame([], $this->callAction(AutocompleteLocationAction::class, ' en '));

        Http::assertNothingSent();
        $this->assertDatabaseEmpty(OpenRouteServiceAutocompleteCache::class);
    }

    #[Test]
    public function itSearchesTownsAndPostcodesInTheUkAndIreland(): void
    {
        $this->fakeAutocompleteResponse();

        $this->callAction(AutocompleteLocationAction::class, 'ennis');

        Http::assertSent(fn (Request $request): bool => $request['text'] === 'ennis'
            && $request['boundary.country'] === 'GB,IE'
            && $request['layers'] === 'locality,localadmin,postalcode,county');
    }

    #[Test]
    public function itReturnsEachUniqueLabelWithItsLatLng(): void
    {
        $this->fakeAutocompleteResponse();

        $this->assertSame([
            ['id' => '1', 'label' => 'Ennis, CE, Ireland', 'lat' => 52.84, 'lng' => -8.98],
            ['id' => '3', 'label' => 'Enniskillen, Northern Ireland, United Kingdom', 'lat' => 54.34, 'lng' => -7.63],
        ], $this->callAction(AutocompleteLocationAction::class, 'ennis'));
    }

    #[Test]
    public function itCachesTheResultsAgainstTheNormalisedTerm(): void
    {
        $this->fakeAutocompleteResponse();

        $this->callAction(AutocompleteLocationAction::class, '  ENNIS ');

        $cache = OpenRouteServiceAutocompleteCache::query()->sole();

        $this->assertSame('ennis', $cache->term);
        $this->assertCount(2, $cache->response);
    }

    #[Test]
    public function itReturnsACachedResultWithoutCallingTheApi(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $results = [['id' => '9', 'label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44]];

        $this->create(OpenRouteServiceAutocompleteCache::class, ['term' => 'crewe', 'response' => $results]);

        $this->assertSame($results, $this->callAction(AutocompleteLocationAction::class, 'Crewe'));

        Http::assertNothingSent();
    }

    #[Test]
    public function itRecordsAHitOnTheCachedResult(): void
    {
        $this->freezeSecond();

        Http::preventStrayRequests();
        Http::fake();

        $cache = $this->create(OpenRouteServiceAutocompleteCache::class, [
            'term' => 'crewe',
            'hits' => 3,
            'most_recent_hit' => now()->subWeek(),
        ]);

        $this->callAction(AutocompleteLocationAction::class, 'crewe');

        $cache->refresh();

        $this->assertSame(4, $cache->hits);
        $this->assertSame(now()->toDateTimeString(), $cache->most_recent_hit->toDateTimeString());
    }
}
