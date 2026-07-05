<?php

declare(strict_types=1);

namespace App\Support\RouteFallbackResolvers;

use App\Actions\Redirects\CheckForRouteRedirectAction;
use App\Actions\Redirects\HandleRedirectResponseAction;
use App\Contracts\RouteFallbackResolverContract;
use App\Models\Redirect;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectFallbackResolver implements RouteFallbackResolverContract
{
    protected ?Redirect $redirect = null;

    public function canHandle(Request $request): bool
    {
        $redirect = app(CheckForRouteRedirectAction::class)->handle($request->path());

        if ($redirect) {
            $this->redirect = $redirect;

            return true;
        }

        return false;
    }

    public function handle(Request $request): Responsable | RedirectResponse
    {
        return app(HandleRedirectResponseAction::class)->handle($this->redirect);
    }
}
