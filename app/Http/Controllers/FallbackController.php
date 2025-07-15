<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CheckForRouteRedirectAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FallbackController
{
    public function __invoke(Request $request, CheckForRouteRedirectAction $checkForRouteRedirectAction): RedirectResponse
    {
        $redirect = $checkForRouteRedirectAction->handle($request->path());

        if ($redirect) {
            $redirect->increment('hits');

            return redirect($redirect->to, $redirect->status);
        }

        abort(Response::HTTP_NOT_FOUND);
    }
}
