<?php

declare(strict_types=1);

namespace App\Http\Controllers\Static\Map;

use App\Services\Static\Map\GoogleMapService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetController
{
    public function __invoke(Request $request, string $latlng, GoogleMapService $googleMapService): Response
    {
        return $googleMapService
            ->renderMap($latlng)
            ->response('jpg');
    }
}
