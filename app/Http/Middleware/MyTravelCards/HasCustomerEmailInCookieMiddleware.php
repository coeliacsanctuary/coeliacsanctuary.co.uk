<?php

declare(strict_types=1);

namespace App\Http\Middleware\MyTravelCards;

use Closure;
use Illuminate\Http\Request;

class HasCustomerEmailInCookieMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ( ! $request->hasCookie('my_travel_cards_email')) {
            return redirect()->route('my-travel-cards.login.show');
        }

        return $next($request);
    }
}
