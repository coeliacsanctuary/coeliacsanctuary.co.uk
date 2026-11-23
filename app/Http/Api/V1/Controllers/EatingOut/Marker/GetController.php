<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Marker;

use App\Actions\EatingOut\RenderEateryMarkerAction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetController
{
    public function __invoke(Request $request, RenderEateryMarkerAction $renderEateryMarkerAction, int $typeId, ?int $venueTypeId = null): Response
    {
        return $renderEateryMarkerAction->handle($request, $typeId, $venueTypeId);
    }
}
