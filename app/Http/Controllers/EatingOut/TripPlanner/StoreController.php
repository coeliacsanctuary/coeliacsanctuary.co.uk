<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\CreateTripAction;
use App\Http\Requests\EatingOut\TripPlannerCreateRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

class StoreController
{
    public function __invoke(TripPlannerCreateRequest $request, CreateTripAction $createTripAction): RedirectResponse
    {
        try {
            $trip = $createTripAction->handle($request->points(), $request->integer('distance'), $request->stopInterval());
        } catch (RequestException) {
            return redirect()
                ->back()
                ->withErrors(['route' => 'Sorry, there was an error planning your trip, please try again.']);
        }

        return new RedirectResponse(route('eating-out.trip-planner.show', ['eateryTrip' => $trip]))
            ->withCookie(new Cookie("trip-planner-{$trip->key}", $trip->edit_token, now()->addYear()));
    }
}
