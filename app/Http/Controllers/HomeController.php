<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Blogs\GetLatestBlogsForHomepageAction;
use App\Actions\Blogs\GetTopBlogsForHomepageAction;
use App\Actions\Collections\GetLatestCollectionsForHomepageAction;
use App\Actions\EatingOut\GetLatestEateriesForHomepageAction;
use App\Actions\EatingOut\GetLatestReviewsForHomepageAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Recipes\GetLatestRecipesForHomepageAction;
use App\Actions\Recipes\GetTopRecipesForHomepageAction;
use App\Http\Response\Inertia;
use Inertia\Response;
use Spatie\SchemaOrg\Schema;

class HomeController
{
    public function __invoke(
        Inertia $inertia,
        GetLatestBlogsForHomepageAction $getLatestBlogsForHomepageAction,
        GetTopBlogsForHomepageAction $getTopBlogsForHomepageAction,
        GetLatestRecipesForHomepageAction $getLatestRecipesForHomepageAction,
        GetTopRecipesForHomepageAction $getTopRecipesForHomepageAction,
        GetLatestCollectionsForHomepageAction $getLatestCollectionsForHomepageAction,
        GetLatestReviewsForHomepageAction $getLatestReviewsForHomepageAction,
        GetLatestEateriesForHomepageAction $getLatestEateriesForHomepageAction,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
    ): Response {
        return $inertia
            ->schema(
                Schema::person()
                    ->name('Alison Peters')
                    ->email('contact@coeliacsanctuary.co.uk')
                    ->sameAs([
                        'https://www.facebook.com/coeliacsanctuary',
                        'https://twitter.com/coeliacsanc',
                        'https://www.instagram.com/coeliacsanctuary',
                    ])
                    ->toScript()
            )
            ->metaImage($getOpenGraphImageForRouteAction->handle())
            ->render('Home', [
                'blogs' => [
                    'top' => $getTopBlogsForHomepageAction->handle(),
                    'latest' => $getLatestBlogsForHomepageAction->handle(),
                ],
                'recipes' => [
                    'top' => $getTopRecipesForHomepageAction->handle(),
                    'latest' => $getLatestRecipesForHomepageAction->handle(),
                ],
                'collections' => $getLatestCollectionsForHomepageAction->handle(),
                'latestReviews' => $getLatestReviewsForHomepageAction->handle(),
                'latestEateries' => $getLatestEateriesForHomepageAction->handle(),
            ]);
    }
}
