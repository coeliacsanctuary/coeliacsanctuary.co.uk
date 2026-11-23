<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner\Email;

use App\Http\Requests\EatingOut\TripPlannerEmailRequest;
use App\Models\EatingOut\EateryTrip;
use App\Notifications\EatingOut\TripPlannerTripNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\AnonymousNotifiable;

class StoreController
{
    public function __invoke(EateryTrip $eateryTrip, TripPlannerEmailRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();

        /** @var string | null $token */
        $token = $request->cookie("trip-planner-{$eateryTrip->key}");

        new AnonymousNotifiable()
            ->route('mail', $email)
            ->notify(new TripPlannerTripNotification($eateryTrip, $eateryTrip->canBeEditedWith($token), $email));

        return redirect()->back();
    }
}
