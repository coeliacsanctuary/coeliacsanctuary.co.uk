<?php

declare(strict_types=1);

namespace App\Actions\Collections;

use App\Models\Collections\Collection;
use App\ResourceCollections\Collections\CollectionListCollection;
use Illuminate\Database\Eloquent\Relations\Relation;

class GetCollectionsForIndexAction
{
    public function handle(int $perPage = 12): CollectionListCollection
    {
        return new CollectionListCollection(
            Collection::query()
                ->with(['media'])
                ->with(['groups' => fn(Relation $relation) => $relation->withCount('items')])
                ->latest('updated_at')
                ->paginate($perPage)
        );
    }
}
