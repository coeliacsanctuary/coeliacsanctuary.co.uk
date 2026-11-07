<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\EatingOut;

use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Infrastructure\MjmlMessage;
use App\Mailables\EatingOut\TripPlannerTripMailable;
use App\Models\EatingOut\EateryTrip;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripPlannerTripMailableTest extends TestCase
{
    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(MjmlMessage::class, TripPlannerTripMailable::make($this->create(EateryTrip::class), false, 'me@example.com', 'foo'));
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = TripPlannerTripMailable::make($this->create(EateryTrip::class), false, 'me@example.com', 'foo');

        $this->assertSame('Your gluten free trip from Crewe, England, United Kingdom to Manchester, England, United Kingdom', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = TripPlannerTripMailable::make($this->create(EateryTrip::class), false, 'me@example.com', 'foo');

        $this->assertSame('mailables.mjml.eating-out.trip-planner-trip', $mailable->mjml);
    }

    #[Test]
    public function itHasTheTripSummaryEmailAndReason(): void
    {
        $data = TripPlannerTripMailable::make($this->create(EateryTrip::class), false, 'me@example.com', 'foo')->data();

        $this->assertSame('Crewe, England, United Kingdom', $data['start']);
        $this->assertSame('Manchester, England, United Kingdom', $data['end']);
        $this->assertSame(35.7, $data['miles']);
        $this->assertSame([0, 54], [$data['hours'], $data['minutes']]);
        $this->assertSame(0, $data['stops']);
        $this->assertSame('me@example.com', $data['email']);
        $this->assertSame('because you asked for a link to your trip', $data['reason']);
    }

    #[Test]
    public function itOnlyCountsTheSuggestedStops(): void
    {
        $this->expectAction(GetTripStopsAction::class, return: [
            ['number' => 1, 'type' => 'via', 'label' => 'Llangollen', 'duration' => 1800.0, 'distanceAlongRoute' => 1000.0, 'lat' => 53.0, 'lng' => -2.0],
            ['number' => 2, 'type' => 'suggested', 'label' => null, 'duration' => 3600.0, 'distanceAlongRoute' => 2000.0, 'lat' => 53.1, 'lng' => -2.1],
        ]);

        $data = TripPlannerTripMailable::make($this->create(EateryTrip::class), false, 'me@example.com', 'foo')->data();

        $this->assertSame(1, $data['stops']);
    }

    #[Test]
    public function itAlwaysIncludesTheViewLink(): void
    {
        $trip = $this->create(EateryTrip::class);

        $data = TripPlannerTripMailable::make($trip, false, 'me@example.com', 'foo')->data();

        $this->assertSame(route('eating-out.trip-planner.show', ['eateryTrip' => $trip->key]), $data['viewUrl']);
    }

    #[Test]
    public function itIncludesTheEditLinkOnlyWhenAsked(): void
    {
        $trip = $this->create(EateryTrip::class);

        $withEdit = TripPlannerTripMailable::make($trip, true, 'me@example.com', 'foo');
        $withoutEdit = TripPlannerTripMailable::make($trip, false, 'me@example.com', 'foo');

        $this->assertSame(route('eating-out.trip-planner.edit', ['eateryTrip' => $trip->key, 'token' => $trip->edit_token]), $withEdit->data()['editUrl']);
        $this->assertNull($withoutEdit->data()['editUrl']);
        $this->assertStringContainsString($trip->edit_token, $withEdit->render());
        $this->assertStringNotContainsString($trip->edit_token, $withoutEdit->render());
    }
}
