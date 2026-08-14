<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

interface RouteFallbackResolverContract
{
    public function canHandle(Request $request): bool;

    public function handle(Request $request): Responsable | RedirectResponse;
}
