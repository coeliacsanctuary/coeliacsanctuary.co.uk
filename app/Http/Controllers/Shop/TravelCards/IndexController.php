<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\TravelCards;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Shop\GetTravelCardReviewsAction;
use App\Actions\Shop\TravelCardSearch\GetPopularTravelCardDestinationsAction;
use App\Actions\Shop\TravelCardSearch\GetTravelCardCategoriesAction;
use App\Actions\Shop\TravelCardSearch\ResolveTravelCardSearchAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use Illuminate\Http\Request;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Request $request,
        Inertia $inertia,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
        ResolveTravelCardSearchAction $resolveTravelCardSearchAction,
        GetPopularTravelCardDestinationsAction $getPopularTravelCardDestinationsAction,
        GetTravelCardCategoriesAction $getTravelCardCategoriesAction,
        GetTravelCardReviewsAction $getTravelCardReviewsAction,
    ): Response {
        $searchTerm = mb_trim($request->string('term')->toString());

        $search = $searchTerm === '' ? null : $resolveTravelCardSearchAction->handle($searchTerm);

        $destinations = $search === null ? '' : collect($search['destinations'])->pluck('term')->join(', ', ' and ');

        return $inertia
            ->title('Gluten Free Travel and Translation Cards' . ($destinations === '' ? '' : " for {$destinations}"))
            ->metaDescription('Travel the world and eat out safely with my range of gluten free travel and translation cards, professionally translated into over 60 languages.')
            ->metaTags([
                'coeliac travel card', 'coeliac translation card', 'gluten free travel card', 'gluten free translation card',
                'allergy card', 'allergy translation card', 'allergy travel card', 'allergen travel card', 'allergen translation card',
            ])
            ->metaImage($getOpenGraphImageForRouteAction->handle('shop'))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop', route('shop.index')),
                new BreadcrumbItemData('Gluten Free Travel and Translation Cards'),
            ]))
            ->render('Shop/TravelCards', [
                'searchTerm' => $searchTerm === '' ? null : $searchTerm,
                'search' => $search,
                'destinations' => fn () => $getPopularTravelCardDestinationsAction->handle(),
                'categories' => fn () => $getTravelCardCategoriesAction->handle(),
                'reviews' => fn () => $getTravelCardReviewsAction->handle(),
            ]);
    }
}
