<?php

declare(strict_types=1);

namespace App\Actions\Collections;

use App\Models\Collections\Collection;
use App\Resources\Collections\CollectionSimpleCardViewResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetLatestCollectionsForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.collections.home');

        /** @var AnonymousResourceCollection $collections */
        $collections = Cache::rememberForever(
            $key,
            fn () => CollectionSimpleCardViewResource::collection(Collection::query()
                ->where('display_on_homepage', true)
                ->latest('updated_at')
                ->with(['media', 'items' => fn (Relation $relation) => $relation->take(8), 'items.item', 'items.item.media'])
                ->get())
        );

        return $collections;
    }
}
