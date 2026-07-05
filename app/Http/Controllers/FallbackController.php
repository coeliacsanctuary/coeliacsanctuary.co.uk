<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\RouteFallbackResolverContract;
use App\Support\RouteFallbackResolvers\RedirectFallbackResolver;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FallbackController
{
    public function __invoke(Request $request): Responsable | RedirectResponse
    {
        /** @var class-string<RouteFallbackResolverContract>[] $resolvers */
        $resolvers = [
            RedirectFallbackResolver::class,
        ];

        return collect($resolvers)
            ->map(fn (string $resolver) => app($resolver))
            ->first(fn (RouteFallbackResolverContract $resolver) => $resolver->canHandle($request))
            ?->handle($request) ?? abort(Response::HTTP_NOT_FOUND);
    }
}
