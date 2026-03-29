<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Collections;

use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCollection;
use App\Pipelines\EatingOut\GetEateries\GetEateriesFromCollectionPipeline;
use App\Resources\EatingOut\EateryCollectionShowResource;
use App\Services\EatingOut\Filters\GetFiltersForCollection;
use Illuminate\Http\Request;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Request $request,
        EateryCollection $eateryCollection,
        GetEateriesFromCollectionPipeline $getEateriesFromCollectionPipeline,
        GetFiltersForCollection $getFiltersForCollection,
        Inertia $inertia,
    ): Response {
        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, towns: string [] | null, counties: string [] | null, areas: string [] | null }  $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
            'towns' => $request->has('filter.town') ? explode(',', $request->string('filter.town')->toString()) : null,
            'counties' => $request->has('filter.county') ? explode(',', $request->string('filter.county')->toString()) : null,
        ];

        return $inertia
            ->title($eateryCollection->title)
            ->metaDescription($eateryCollection->meta_description)
            ->metaTags(explode(',', $eateryCollection->meta_tags))
            ->metaImage($eateryCollection->social_image)
            ->alternateMetas([
                'article:publisher' => 'https://www.facebook.com/coeliacsanctuary',
                'article:section' => 'Food',
                'article:published_time' => $eateryCollection->created_at,
                'article:modified_time' => $eateryCollection->updated_at,
                'article:author' => 'Coeliac Sanctuary',
                'article.tags' => $eateryCollection->meta_tags,
            ])
            ->schema($eateryCollection->schema()->toScript())
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.landing')),
                new BreadcrumbItemData('Collections', route('eating-out.collections.index')),
                new BreadcrumbItemData($eateryCollection->title),
            ]))
            ->metaFeed(route('eating-out.collections.feed'))
            ->render('EatingOut/Collections/Show', [
                'collection' => new EateryCollectionShowResource($eateryCollection),
                'filters' => $inertia->defer(fn () => $getFiltersForCollection->setCollection($eateryCollection)->handle($filters)),
                'eateries' => $inertia->defer(fn () => $getEateriesFromCollectionPipeline->run($eateryCollection->configuration, $filters)),
            ]);
    }
}
