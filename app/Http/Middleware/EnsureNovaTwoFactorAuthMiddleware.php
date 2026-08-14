<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureNovaTwoFactorAuthMiddleware
{
    protected string $settingsPath = 'cs-adm/user-security';

    public function handle(Request $request, Closure $next): mixed
    {
        if ($this->shouldRedirect($request->user(), $request->path())) {
            return redirect()
                ->to($this->settingsPath)
                ->with('nova_flash', 'Please setup two factor authentication.');
        }

        return $next($request);
    }

    protected function shouldRedirect(?User $user, string $path): bool
    {
        if ( ! $user) {
            return false;
        }

        if (str_contains($path, $this->settingsPath)) {
            return false;
        }

        if (str_contains($path, 'nova-api/styles')) {
            return false;
        }

        if (str_contains($path, 'nova-api/scripts')) {
            return false;
        }

        if ( ! app()->isProduction()) {
            return false;
        }

        return $user->two_factor_secret === null;
    }
}
