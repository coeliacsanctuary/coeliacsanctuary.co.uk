<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Browse;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Response\Inertia;
use App\Models\OpenGraphImage;
use Illuminate\Support\Facades\URL;
use Inertia\Response;

class ShowController
{
    public function __invoke(Inertia $inertia, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        $ogImage = $getOpenGraphImageForRouteAction->handle('eatery', function(string $url, OpenGraphImage $image) {
            $timestamp = $image->updated_at->timestamp;

            return URL::query($url, ['cache' => $timestamp]);
        });

        return $inertia
            ->title('Gluten Free Places to Eat Map')
            ->metaDescription('Coeliac Sanctuary where to eat map | Places in the UK who can cater to Coeliac and gluten free diets')
            ->metaTags([
                'gluten free places to eat', 'gluten free cafes', 'gluten free restaurants', 'gluten free uk',
                'places to eat', 'cafes', 'restaurants', 'eating out', 'catering to coeliac', 'eating out uk',
                'gluten free venues', 'gluten free dining', 'gluten free directory', 'gf food',
                'gluten free eating out uk', 'uk places to eat', 'gluten free attractions', 'gluten free hotels',
            ])
            ->metaImage($ogImage)
            ->render('EatingOut/Browse');
    }
}
