<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner\Edit;

use App\Models\EatingOut\EateryTrip;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    #[Test]
    public function itRedirectsToTheTripWithAValidToken(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->get(route('eating-out.trip-planner.edit', ['eateryTrip' => $trip->key, 'token' => $trip->edit_token]))
            ->assertRedirect(route('eating-out.trip-planner.show', ['eateryTrip' => $trip->key]));
    }

    #[Test]
    public function itStoresTheEditTokenInACookieForTheTrip(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->get(route('eating-out.trip-planner.edit', ['eateryTrip' => $trip->key, 'token' => $trip->edit_token]))
            ->assertCookie("trip-planner-{$trip->key}", $trip->edit_token);
    }

    #[Test]
    public function itReturnsNotFoundForAWrongToken(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->get(route('eating-out.trip-planner.edit', ['eateryTrip' => $trip->key, 'token' => 'not-the-token']))
            ->assertNotFound()
            ->assertCookieMissing("trip-planner-{$trip->key}");
    }

    #[Test]
    public function itReturnsNotFoundForAnUnknownTrip(): void
    {
        $this->get(route('eating-out.trip-planner.edit', ['eateryTrip' => 'NOTATRIP', 'token' => 'anything']))
            ->assertNotFound();
    }
}
