<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\Browse\Search;

use PHPUnit\Framework\Attributes\Test;
use App\DataObjects\EatingOut\LatLng;
use App\Services\EatingOut\LocationSearchService;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function makeRequest(?string $term = null): TestResponse
    {
        return $this->postJson(route('api.wheretoeat.browse.search', ['term' => $term]));
    }

    #[Test]
    public function itReturnsAnErrorWithoutASearchTerm(): void
    {
        $this->makeRequest()->assertJsonValidationErrorFor('term');
    }

    #[Test]
    public function itReturnsAnErrorIfTheSearchTermIsTooShort(): void
    {
        $this->makeRequest('ab')->assertJsonValidationErrorFor('term');
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $this->mock(LocationSearchService::class)
            ->expects('getLatLng')
            ->zeroOrMoreTimes()
            ->andReturn(new LatLng($london['lat'], $london['lng']));

        $this->makeRequest('london')->assertOk();
    }

    #[Test]
    public function itReturnsTheLatLngForAResponse(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $this->mock(LocationSearchService::class)
            ->expects('getLatLng')
            ->zeroOrMoreTimes()
            ->andReturn(new LatLng($london['lat'], $london['lng']));

        $response = $this->makeRequest('london')->json();

        $this->assertEquals(round($response['lat'], 1), $london['lat']);
        $this->assertEquals(round($response['lng'], 1), $london['lng']);
    }
}
