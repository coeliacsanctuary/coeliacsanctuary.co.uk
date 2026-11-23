<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner\Edit;

use App\Models\EatingOut\EateryTrip;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

class ShowController
{
    public function __invoke(EateryTrip $eateryTrip, string $token): RedirectResponse
    {
        abort_unless($eateryTrip->canBeEditedWith($token), 404);

        return new RedirectResponse(route('eating-out.trip-planner.show', ['eateryTrip' => $eateryTrip]))
            ->withCookie(new Cookie("trip-planner-{$eateryTrip->key}", $eateryTrip->edit_token, now()->addYear()));
    }
}
