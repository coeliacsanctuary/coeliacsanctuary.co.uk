<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\AutocompleteLocationAction;
use App\Models\GooglePlacesAutocompleteCache;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use SKAgarwal\GoogleApi\PlacesNew\GooglePlaces;
use SKAgarwal\GoogleApi\PlacesNew\Requests\Autocomplete;
use Tests\TestCase;

class AutocompleteLocationActionTest extends TestCase
{
    protected function mockGoogle(?MockResponse $response = null): MockClient
    {
        $mockClient = new MockClient([
            Autocomplete::class => $response ?? MockResponse::make([
                'suggestions' => [
                    ['placePrediction' => ['placeId' => 'place-1', 'text' => ['text' => '5 Queen Street, Crewe, UK']]],
                    ['queryPrediction' => ['text' => ['text' => '5 queen street crewe']]],
                    ['placePrediction' => ['placeId' => 'place-2', 'text' => ['text' => '5 Queen Street, Chester, UK']]],
                ],
            ]),
        ]);

        $this->app->instance(GooglePlaces::class, new GooglePlaces()->withMockClient($mockClient));

        return $mockClient;
    }

    #[Test]
    public function itReturnsNothingForATermUnderThreeCharactersWithoutCallingGoogle(): void
    {
        $mockClient = $this->mockGoogle();

        $this->assertSame([], $this->callAction(AutocompleteLocationAction::class, ' 5q ', 'token'));

        $mockClient->assertNothingSent();
        $this->assertDatabaseEmpty(GooglePlacesAutocompleteCache::class);
    }

    #[Test]
    public function itSearchesTheUkIrelandAndCrownDependenciesWithTheSessionToken(): void
    {
        $mockClient = $this->mockGoogle();

        $this->callAction(AutocompleteLocationAction::class, '5 Queen Street Crewe', 'session-token');

        $mockClient->assertSent(fn (Request $request, Response $response): bool => $response->getPendingRequest()->body()?->all() === [
            'input' => '5 queen street crewe',
            'includeQueryPredictions' => false,
            'includedRegionCodes' => ['gb', 'ie', 'je', 'gg', 'im'],
            'sessionToken' => 'session-token',
        ]);
    }

    #[Test]
    public function itReturnsEachPlacePredictionsIdAndLabel(): void
    {
        $this->mockGoogle();

        $this->assertSame([
            ['id' => 'place-1', 'label' => '5 Queen Street, Crewe, UK'],
            ['id' => 'place-2', 'label' => '5 Queen Street, Chester, UK'],
        ], $this->callAction(AutocompleteLocationAction::class, '5 queen street crewe', 'token'));
    }

    #[Test]
    public function itCachesTheResultsAgainstTheNormalisedTerm(): void
    {
        $this->freezeSecond();

        $this->mockGoogle();

        $this->callAction(AutocompleteLocationAction::class, '  5 QUEEN Street   Crewe ', 'token');

        $cache = GooglePlacesAutocompleteCache::query()->sole();

        $this->assertSame('5 queen street crewe', $cache->term);
        $this->assertCount(2, $cache->response);
        $this->assertSame(now()->toDateTimeString(), $cache->last_fetched_at->toDateTimeString());
    }

    #[Test]
    public function itReturnsAFreshCachedResultWithoutCallingGoogle(): void
    {
        $mockClient = $this->mockGoogle();

        $results = [['id' => 'cached', 'label' => 'Crewe, UK']];

        $this->create(GooglePlacesAutocompleteCache::class, ['term' => 'crewe', 'response' => $results, 'last_fetched_at' => now()->subDays(29)]);

        $this->assertSame($results, $this->callAction(AutocompleteLocationAction::class, 'Crewe', 'token'));

        $mockClient->assertNothingSent();
    }

    #[Test]
    public function itRecordsAHitOnAFreshCachedResult(): void
    {
        $this->mockGoogle();

        $cache = $this->create(GooglePlacesAutocompleteCache::class, ['term' => 'crewe', 'hits' => 3]);

        $this->callAction(AutocompleteLocationAction::class, 'crewe', 'token');

        $this->assertSame(4, $cache->refresh()->hits);
    }

    #[Test]
    public function itRefetchesAndOverwritesACachedResultOlderThanThirtyDays(): void
    {
        $this->freezeSecond();

        $mockClient = $this->mockGoogle();

        $cache = $this->create(GooglePlacesAutocompleteCache::class, [
            'term' => '5 queen street crewe',
            'response' => [['id' => 'stale', 'label' => 'Stale']],
            'last_fetched_at' => now()->subDays(31),
        ]);

        $results = $this->callAction(AutocompleteLocationAction::class, '5 queen street crewe', 'token');

        $mockClient->assertSentCount(1);
        $this->assertSame('place-1', $results[0]['id']);

        $cache->refresh();

        $this->assertDatabaseCount(GooglePlacesAutocompleteCache::class, 1);
        $this->assertSame('place-1', $cache->response[0]['id']);
        $this->assertSame(now()->toDateTimeString(), $cache->last_fetched_at->toDateTimeString());
    }

    #[Test]
    public function itReturnsNothingAndCachesNothingWhenGoogleFails(): void
    {
        $this->mockGoogle(MockResponse::make(['error' => ['message' => 'bad']], 500));

        $this->assertSame([], $this->callAction(AutocompleteLocationAction::class, '5 queen street crewe', 'token'));

        $this->assertDatabaseEmpty(GooglePlacesAutocompleteCache::class);
    }
}
