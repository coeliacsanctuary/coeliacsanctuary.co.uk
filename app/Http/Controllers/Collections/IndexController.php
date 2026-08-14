<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collections;

use App\Actions\Collections\GetCollectionsForIndexAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Response\Inertia;
use Inertia\Response;

class IndexController
{
    public function __invoke(Inertia $inertia, GetCollectionsForIndexAction $getCollectionsForIndexAction, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        return $inertia
            ->title('Gluten Free Collections')
            ->metaDescription('Gluten free collections from Coeliac Sanctuary — recipes, blog posts and coeliac friendly places to eat, grouped together by theme so everything you need is on one page.')
            ->metaImage($getOpenGraphImageForRouteAction->handle('collection'))
            ->render('Collection/Index', [
                'collections' => $getCollectionsForIndexAction->handle(),
            ]);
    }
}
