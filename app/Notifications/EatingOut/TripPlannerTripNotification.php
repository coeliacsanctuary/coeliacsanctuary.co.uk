<?php

declare(strict_types=1);

namespace App\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Infrastructure\Notification;
use App\Mailables\EatingOut\TripPlannerTripMailable;
use App\Models\EatingOut\EateryTrip;
use App\Models\Shop\ShopCustomer;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

class TripPlannerTripNotification extends Notification
{
    public function __construct(protected EateryTrip $trip, protected bool $includeEditLink, protected string $email)
    {
        //
    }

    public function toMail(User|ShopCustomer|AnonymousNotifiable|null $notifiable = null): MjmlMessage
    {
        return TripPlannerTripMailable::make($this->trip, $this->includeEditLink, $this->email, $this->key);
    }
}
