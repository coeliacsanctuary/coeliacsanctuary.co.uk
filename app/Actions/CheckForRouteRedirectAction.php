<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Redirect;
use Illuminate\Support\Str;

class CheckForRouteRedirectAction
{
    public function handle(string $path): ?Redirect
    {
        $path = Str::of($path)
            ->trim('/')
            ->prepend('/')
            ->toString();

        return Redirect::query()
            ->whereRaw('? regexp replace(`from`, ?, ?)', [$path, '*', '.+'])
            ->first();
    }
}
