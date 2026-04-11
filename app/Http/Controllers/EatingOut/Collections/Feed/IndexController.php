<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Collections\Feed;

use App\Actions\EatingOut\GetCollectionsForCollectionIndexAction;
use App\Feeds\EateryCollectionFeed;
use App\Models\EatingOut\EateryCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class IndexController
{
    public function __invoke(GetCollectionsForCollectionIndexAction $getCollectionsForCollectionIndexAction, EateryCollectionFeed $eateryCollectionFeed): Response
    {
        /** @var Collection<int, EateryCollection> $collections */
        $collections = $getCollectionsForCollectionIndexAction
            ->handle()
            ->collection
            ?->map(fn (JsonResource $resource) => $resource->resource);

        return new Response(
            $eateryCollectionFeed->render($collections),
            Response::HTTP_OK,
            ['Content-Type' => 'text/xml'],
        );
    }
}
