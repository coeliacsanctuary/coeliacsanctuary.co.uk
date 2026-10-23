<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Marker;

use App\Actions\EatingOut\RenderEateryMarkerAction;
use App\Models\EatingOut\EateryType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->dontExpectAction(RenderEateryMarkerAction::class);

        $this->get(route('api.v1.eating-out.marker.get', ['typeId' => EateryType::EATERY]))->assertForbidden();
    }

    #[Test]
    public function itCallsTheRenderEateryMarkerActionWithTheTypeAndVenueType(): void
    {
        $this->expectAction(RenderEateryMarkerAction::class, [
            fn (Request $request, int $typeId, ?int $venueTypeId) => $typeId === EateryType::ATTRACTION && $venueTypeId === 123,
        ], return: new Response('<svg></svg>'));

        $this->makeRequest(EateryType::ATTRACTION, 123)->assertOk();
    }

    #[Test]
    public function itCallsTheRenderEateryMarkerActionWithNoVenueType(): void
    {
        $this->expectAction(RenderEateryMarkerAction::class, [
            fn (Request $request, int $typeId, ?int $venueTypeId) => $typeId === EateryType::HOTEL && $venueTypeId === null,
        ], return: new Response('<svg></svg>'));

        $this->makeRequest(EateryType::HOTEL)->assertOk();
    }

    #[Test]
    public function itReturnsTheResponseFromTheAction(): void
    {
        $this->expectAction(
            RenderEateryMarkerAction::class,
            return: new Response('<svg>the-marker</svg>', 200, ['Content-Type' => 'image/svg+xml']),
        );

        $response = $this->makeRequest();

        $response->assertOk();
        $response->assertHeader('content-type', 'image/svg+xml');

        $this->assertEquals('<svg>the-marker</svg>', $response->getContent());
    }

    #[Test]
    public function itReturnsANotFoundForNonNumericIds(): void
    {
        $this->dontExpectAction(RenderEateryMarkerAction::class);

        $this->get('/api/v1/eating-out/marker/foo', ['x-coeliac-source' => 'bar'])->assertNotFound();
    }

    protected function makeRequest(int $typeId = EateryType::EATERY, ?int $venueTypeId = null, string $source = 'bar'): TestResponse
    {
        return $this->get(
            route('api.v1.eating-out.marker.get', array_filter([
                'typeId' => $typeId,
                'venueTypeId' => $venueTypeId,
            ])),
            ['x-coeliac-source' => $source],
        );
    }
}
