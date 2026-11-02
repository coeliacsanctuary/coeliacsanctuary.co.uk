<?php

declare(strict_types=1);

namespace App\Http\Controllers\Popup\Activity;

use App\Models\Popup;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Cookie;

class StoreController
{
    public function __invoke(Popup $popup): Response
    {
        return new Response()->withCookie(new Cookie(
            "CS_SEEN_POPUP_{$popup->id}",
            (string) now()->timestamp,
            now()->addDays($popup->display_every),
        ));
    }
}
