<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Collections;

use App\Actions\EatingOut\GetCollectionsForCollectionIndexAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Inertia $inertia,
        GetCollectionsForCollectionIndexAction $getCollectionsForCollectionIndexAction,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
    ): Response {
        return $inertia
            ->title('Gluten Free Eating Out Collections')
            ->metaDescription('Curated collections of places to eat out at around the UK')
            ->metaTags(['coeliac sanctuary eating out', 'eating out uk'])
            ->metaImage($getOpenGraphImageForRouteAction->handle('eatery-collection'))
            ->metaFeed(route('eating-out.collections.feed'))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.landing')),
                new BreadcrumbItemData('Collections'),
            ]))
            ->render('EatingOut/Collections/Index', [
                'collections' => fn () => $getCollectionsForCollectionIndexAction->handle(),
            ]);
    }
}
