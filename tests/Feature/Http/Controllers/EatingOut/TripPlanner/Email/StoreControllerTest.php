<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\TripPlanner\Email;

use App\Models\EatingOut\EateryTrip;
use App\Notifications\EatingOut\TripPlannerTripNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected EateryTrip $trip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trip = $this->create(EateryTrip::class);
    }

    protected function makeRequest(?string $email = 'me@example.com', bool $withToken = false): TestResponse
    {
        if ($withToken) {
            $this->withCookie("trip-planner-{$this->trip->key}", $this->trip->edit_token);
        }

        return $this->from(route('eating-out.trip-planner.show', ['eateryTrip' => $this->trip->key]))
            ->post(route('eating-out.trip-planner.email', ['eateryTrip' => $this->trip->key]), ['email' => $email]);
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith(['not-an-email'])]
    public function itRejectsAMissingOrInvalidEmailAndSendsNothing(?string $email): void
    {
        Notification::fake();

        $this->makeRequest($email)->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    #[Test]
    public function itEmailsTheTripToTheGivenAddress(): void
    {
        Notification::fake();

        $this->makeRequest()->assertRedirect(route('eating-out.trip-planner.show', ['eateryTrip' => $this->trip->key]));

        Notification::assertSentOnDemand(
            TripPlannerTripNotification::class,
            fn (TripPlannerTripNotification $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'me@example.com',
        );
    }

    #[Test]
    public function itIncludesTheEditLinkWhenTheBrowserHoldsTheEditToken(): void
    {
        Notification::fake();

        $this->makeRequest(withToken: true);

        Notification::assertSentOnDemand(
            TripPlannerTripNotification::class,
            fn (TripPlannerTripNotification $notification): bool => $notification->toMail()->data()['editUrl'] !== null,
        );
    }

    #[Test]
    public function itLeavesOutTheEditLinkWithoutTheEditToken(): void
    {
        Notification::fake();

        $this->makeRequest();

        Notification::assertSentOnDemand(
            TripPlannerTripNotification::class,
            fn (TripPlannerTripNotification $notification): bool => $notification->toMail()->data()['editUrl'] === null,
        );
    }

    #[Test]
    public function itDoesNotStoreAnythingAboutTheEmail(): void
    {
        Notification::fake();

        $before = $this->trip->refresh()->getAttributes();

        $this->makeRequest(withToken: true);

        $this->assertSame($before, $this->trip->refresh()->getAttributes());
        $this->assertDatabaseCount(EateryTrip::class, 1);
    }

    #[Test]
    public function itLimitsHowOftenTheTripCanBeEmailed(): void
    {
        Notification::fake();

        foreach (range(1, 5) as $attempt) {
            $this->makeRequest()->assertRedirect();
        }

        $this->makeRequest()->assertTooManyRequests();
    }
}
