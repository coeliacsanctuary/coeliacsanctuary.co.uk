<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\Lookup;

use App\Actions\EatingOut\GetPlaceLocationAction;
use App\DataObjects\EatingOut\LatLng;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected function makeRequest(?string $sessionToken = 'session-token'): TestResponse
    {
        return $this->getJson(route('api.wheretoeat.lookup.get', ['placeId' => 'place-1', 'sessionToken' => $sessionToken]));
    }

    #[Test]
    public function itReturnsAValidationErrorWithoutASessionToken(): void
    {
        $this->dontExpectAction(GetPlaceLocationAction::class);

        $this->makeRequest(null)->assertJsonValidationErrorFor('sessionToken');
    }

    #[Test]
    public function itReturnsThePlacesLatLng(): void
    {
        $this->expectAction(GetPlaceLocationAction::class, ['place-1', 'session-token'], return: new LatLng(53.0957, -2.4421));

        $this->makeRequest()
            ->assertOk()
            ->assertExactJson(['lat' => 53.0957, 'lng' => -2.4421]);
    }

    #[Test]
    public function itReturnsNotFoundWhenThePlaceCannotBeFound(): void
    {
        $this->expectAction(GetPlaceLocationAction::class, then: fn ($expectation) => $expectation->andThrow(new RuntimeException('not found')));

        $this->makeRequest()->assertNotFound();
    }
}
