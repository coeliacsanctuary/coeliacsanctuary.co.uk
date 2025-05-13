<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Shop\AddressSearch;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    #[Test]
    public function itReturnsAnErrorWithoutASearchString(): void
    {
        $this->postJson(route('api.shop.address-search'))
            ->assertJsonValidationErrorFor('search');
    }

    #[Test]
    public function itErrorsWithAnInvalidSearchString(): void
    {
        $this->postJson(route('api.shop.address-search'), [
            'search' => true,
        ])->assertJsonValidationErrorFor('search');

        $this->postJson(route('api.shop.address-search'), [
            'search' => 123,
        ])->assertJsonValidationErrorFor('search');
    }

    #[Test]
    public function itCallsTheSearchService(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([]),
        ]);

        $this->postJson(route('api.shop.address-search'), [
            'search' => 'foo',
        ])->assertOk();

        Http::assertSent(function (Request $request) {
            $this->assertEquals('POST', $request->method());
            $this->assertStringContainsString(config('services.getAddress.url'), $request->url());
            $this->assertStringContainsString('/autocomplete/foo', $request->url());

            return true;
        });
    }

    #[Test]
    public function itSendsTheLatLngIfRequested(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([]),
        ]);

        $this->postJson(route('api.shop.address-search'), [
            'search' => 'foo',
            'lat' => 12.34,
            'lng' => 56.78,
        ])->assertOk();

        Http::assertSent(function (Request $request) {
            $this->assertArrayHasKey('location', $request->data());
            $this->assertArrayHasKey('latitude', $request->data()['location']);
            $this->assertArrayHasKey('longitude', $request->data()['location']);
            $this->assertEquals(12.34, $request->data()['location']['latitude']);
            $this->assertEquals(56.78, $request->data()['location']['longitude']);

            return true;
        });
    }

    #[Test]
    public function itErrorsIfSendingLatWithoutLngAndViceVersa(): void
    {
        $this->postJson(route('api.shop.address-search'), [
            'lat' => 123,
        ])->assertJsonValidationErrorFor('lng');

        $this->postJson(route('api.shop.address-search'), [
            'lng' => 123,
        ])->assertJsonValidationErrorFor('lat');
    }

    #[Test]
    public function itReturnsTheResults(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'suggestions' => [
                    ['id' => 'foo', 'address' => 'bar', 'a' => 'b'],
                    ['id' => 'hello', 'address' => 'there', 'c' => 'd'],
                ],
            ]),
        ]);

        $this->postJson(route('api.shop.address-search'), [
            'search' => 'foo',
            'lat' => 12.34,
            'lng' => 56.78,
        ])->assertOk()
            ->assertJson([
                ['id' => 'foo', 'address' => 'bar'],
                ['id' => 'hello', 'address' => 'there'],
            ]);
    }
}
