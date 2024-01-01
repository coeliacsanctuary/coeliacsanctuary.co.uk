<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collections;

use App\Http\Response\Inertia;
use App\Models\Collections\Collection;
use App\Resources\Collections\CollectionShowResource;
use Inertia\Response;

class CollectionShowController
{
    public function __invoke(Inertia $inertia, Collection $collection): Response
    {
        $collection->load(['items', 'items.item', 'items.item.media']);

        return $inertia
            ->title($collection->title)
            ->metaDescription($collection->meta_description)
            ->metaTags(explode(',', $collection->meta_tags))
            ->metaImage($collection->social_image)
            ->render('Collection/Show', [
                'collection' => new CollectionShowResource($collection),
            ]);

    }
}
