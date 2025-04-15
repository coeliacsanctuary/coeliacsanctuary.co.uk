<?php

declare(strict_types=1);

namespace App\Http\Controllers\Feed;

use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\Actions\Recipes\GetRecipesForIndexAction;
use App\Feeds\CombinedFeed;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class IndexController
{
    public function __invoke(
        GetBlogsForBlogIndexAction $getBlogsForBlogIndexAction,
        GetRecipesForIndexAction $getRecipesForIndexAction,
        CombinedFeed $combinedFeed,
    ): Response {
        /** @var Collection<int, Blog> $blogs */
        $blogs = $getBlogsForBlogIndexAction
            ->handle()
            ->collection
            ->map(fn (JsonResource $resource) => $resource->resource);

        /** @var Collection<int, Recipe> $recipes */
        $recipes = $getRecipesForIndexAction
            ->handle()
            ->collection
            ->map(fn (JsonResource $resource) => $resource->resource);

        /** @var Collection<int, Blog | Recipe> $items */
        $items = collect([...$blogs, ...$recipes])
            ->sortByDesc('created_at')
            ->take(10);

        return new Response(
            $combinedFeed->render($items),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml'],
        );
    }
}
