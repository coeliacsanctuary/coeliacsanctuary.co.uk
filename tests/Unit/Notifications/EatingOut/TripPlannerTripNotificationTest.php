<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\EatingOut;

use App\Models\EatingOut\EateryTrip;
use App\Notifications\EatingOut\TripPlannerTripNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripPlannerTripNotificationTest extends TestCase
{
    #[Test]
    public function itRendersTheTripEmailWithItsKeyAndViewLink(): void
    {
        Notification::fake();

        $trip = $this->create(EateryTrip::class);

        new AnonymousNotifiable()
            ->route('mail', 'me@example.com')
            ->notify(new TripPlannerTripNotification($trip, false, 'me@example.com'));

        Notification::assertSentOnDemand(TripPlannerTripNotification::class, function (TripPlannerTripNotification $notification) use ($trip): bool {
            $mail = $notification->toMail(new AnonymousNotifiable());
            $content = $mail->render();

            $this->assertStringContainsString($mail->data()['key'], $content);
            $this->assertStringContainsString(route('eating-out.trip-planner.show', ['eateryTrip' => $trip->key]), $content);
            $this->assertStringContainsString('me@example.com', $content);

            return true;
        });
    }
}
