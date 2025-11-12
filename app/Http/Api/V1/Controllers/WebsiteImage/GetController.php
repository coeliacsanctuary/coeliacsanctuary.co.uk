<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\WebsiteImage;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;

class GetController
{
    public function __invoke(GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): array
    {
        return [
            'data' => $getOpenGraphImageForRouteAction->handle(),
        ];
    }
}
