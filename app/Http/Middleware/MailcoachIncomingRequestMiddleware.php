<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MailcoachIncomingRequestMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if ($request->get('key') !== config('mailcoach-sdk.incoming-key')) {
            abort(403);
        }

        return $next($request);
    }
}
