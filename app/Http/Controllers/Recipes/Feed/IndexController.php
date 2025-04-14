<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recipes\Feed;

use App\Actions\Recipes\GetRecipesForIndexAction;
use App\Feeds\RecipeFeed;
use App\Models\Recipes\Recipe;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class IndexController
{
    public function __invoke(GetRecipesForIndexAction $getRecipesForIndexAction, RecipeFeed $recipeFeed): Response
    {
        /** @var Collection<int, Recipe> $recipes */
        $recipes = $getRecipesForIndexAction
            ->handle()
            ->collection
            ->map(fn (JsonResource $resource) => $resource->resource);

        return new Response(
            $recipeFeed->render($recipes),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml'],
        );
    }
}
