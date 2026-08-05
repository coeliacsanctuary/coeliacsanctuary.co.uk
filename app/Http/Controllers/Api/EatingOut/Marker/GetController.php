<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Marker;

use App\Enums\EatingOut\EateryType;
use App\Services\EatingOut\MarkerGlyphService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetController
{
    public function __construct(
        protected Factory $viewFactory,
        protected MarkerGlyphService $markerGlyphService,
    ) {
        //
    }

    public function __invoke(Request $request, int $typeId, ?int $venueTypeId = null): Response
    {
        $marker = $this->viewFactory->make('markers.pin', [
            'color' => (EateryType::tryFrom($typeId) ?? EateryType::EATERY)->color(),
            'glyph' => $this->markerGlyphService->resolve($typeId, $venueTypeId),
        ])->render();

        $response = new Response($marker, 200, ['Content-Type' => 'image/svg+xml']);

        if (app()->isLocal()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

            return $response;
        }

        $response->setEtag(md5($marker));
        $response->setPublic();
        $response->setMaxAge(3600);
        $response->headers->addCacheControlDirective('must-revalidate');

        $response->isNotModified($request);

        return $response;
    }
}
