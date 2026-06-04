<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\LandingPage;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Response\Inertia;
use App\Models\OpenGraphImage;
use Illuminate\Support\Facades\URL;
use Inertia\Response;

class IndexController
{
    public function __invoke(Inertia $inertia, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        $ogImage = $getOpenGraphImageForRouteAction->handle('eatery', function(string $url, OpenGraphImage $image) {
            $timestamp = $image->updated_at->timestamp;

            return URL::query($url, ['cache' => $timestamp]);
        });

        return $inertia
            ->title('Gluten Free Eating Out')
            ->metaDescription('Coeliac Sanctuary places to eat overview | Find gluten free places to eat in the UK, attractions and hotels catering to Coeliac and reviews of places')
            ->metaImage($ogImage)
            ->metaTags([
                'Gluten free eating out', 'coeliac eating out', 'reviews', 'places to eat', 'uk places to eat', 'gluten free places to eat uk',
                'attractions uk gluten free', 'gluten free reviews', 'coeliac reviews', 'gluten free hotels', 'gluten free restaurants uk',
                'gluten free cafes uk',
            ])
            ->render('EatingOut/LandingNew');
    }
}
