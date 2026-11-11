<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireWebBasicAuthMiddleware
{
    public function __construct(protected AuthFactory $auth)
    {
        //
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment(['production', 'local', 'testing']) || config('coeliac.enable_web_access')) {
            return $next($request);
        }

        $isValidUser = $this->auth->guard('web')->validate([
            'email' => $request->getUser(),
            'password' => $request->getPassword(),
        ]);

        abort_unless($isValidUser, 401, headers: ['WWW-Authenticate' => 'Basic']);

        return $next($request);
    }
}
