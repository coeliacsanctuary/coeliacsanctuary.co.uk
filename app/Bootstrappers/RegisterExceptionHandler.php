<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use App\Actions\Bootstrap\RespondToExceptionAction;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class RegisterExceptionHandler
{
    public static function handle(Exceptions $exceptions): Exceptions
    {
        $exceptions->render(function (InvalidSignatureException $exception, Request $request) {
            if ($request->routeIs('my-travel-cards.login.order.get')) {
                return redirect()
                    ->route('my-travel-cards.login.show')
                    ->withErrors(['email' => 'Your link has expired, please enter your email to access your travel cards']);
            }

            return null;
        });

        $exceptions->respond(app(RespondToExceptionAction::class)->handle(...));

        return $exceptions;
    }
}
