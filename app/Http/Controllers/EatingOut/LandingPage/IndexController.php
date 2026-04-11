<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\LandingPage;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Response\Inertia;
use Inertia\Response;

class IndexController
{
    public function __invoke(Inertia $inertia, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        return $inertia
            ->title('Gluten Free Eating Out')
            ->metaDescription('Coeliac Sanctuary places to eat overview | Find gluten free places to eat in the UK, attractions and hotels catering to Coeliac and reviews of places')
            ->metaImage($getOpenGraphImageForRouteAction->handle('eatery'))
            ->metaTags([
                'Gluten free eating out', 'coeliac eating out', 'reviews', 'places to eat', 'uk places to eat', 'gluten free places to eat uk',
                'attractions uk gluten free', 'gluten free reviews', 'coeliac reviews', 'gluten free hotels', 'gluten free restaurants uk',
                'gluten free cafes uk',
            ])
            ->render(config('coeliac.enable_eatery_collections') ? 'EatingOut/LandingNew' : 'EatingOut/Landing');
    }
}
