<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\UpdateTripAction;
use App\Http\Requests\EatingOut\TripPlannerUpdateRequest;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;

class UpdateController
{
    public function __invoke(EateryTrip $eateryTrip, TripPlannerUpdateRequest $request, UpdateTripAction $updateTripAction): RedirectResponse
    {
        try {
            $updateTripAction->handle($eateryTrip, $request->points(), $request->integer('distance'), $request->stopInterval());
        } catch (RequestException) {
            return redirect()
                ->back()
                ->withErrors(['route' => 'Sorry, there was an error planning your trip, please try again.']);
        }

        return new RedirectResponse(route('eating-out.trip-planner.show', ['eateryTrip' => $eateryTrip]));
    }
}
