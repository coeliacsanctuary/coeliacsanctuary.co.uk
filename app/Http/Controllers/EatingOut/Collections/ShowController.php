<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Collections;

use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCollection;
use App\Resources\EatingOut\EateryCollectionShowResource;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        EateryCollection $eateryCollection,
        Inertia $inertia,
    ): Response {
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
            ]);
    }
}
