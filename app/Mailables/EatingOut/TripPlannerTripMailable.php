<?php

declare(strict_types=1);

namespace App\Mailables\EatingOut;

use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Infrastructure\MjmlMessage;
use App\Mailables\BaseMailable;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Support\Arr;

class TripPlannerTripMailable extends BaseMailable
{
    public function __construct(protected EateryTrip $trip, protected bool $includeEditLink, protected string $email, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    public function toMail(): MjmlMessage
    {
        $start = Arr::first($this->trip->points)['label'] ?? '';
        $end = Arr::last($this->trip->points)['label'] ?? '';

        return MjmlMessage::make()
            ->subject("Your gluten free trip from {$start} to {$end}")
            ->mjml('mailables.mjml.eating-out.trip-planner-trip', $this->baseData([
                'start' => $start,
                'end' => $end,
                'miles' => round($this->trip->route_distance / 1609.344, 1),
                'hours' => intdiv((int) $this->trip->route_duration, 3600),
                'minutes' => (int) round(((int) $this->trip->route_duration % 3600) / 60),
                'stops' => collect(app(GetTripStopsAction::class)->handle($this->trip->toTripRoute(), $this->trip->points, $this->trip->stop_interval))->where('type', 'suggested')->count(),
                'viewUrl' => route('eating-out.trip-planner.show', ['eateryTrip' => $this->trip]),
                'editUrl' => $this->includeEditLink ? route('eating-out.trip-planner.edit', ['eateryTrip' => $this->trip, 'token' => $this->trip->edit_token]) : null,
                'reason' => 'because you asked for a link to your trip',
                'email' => $this->email,
            ]));
    }
}
