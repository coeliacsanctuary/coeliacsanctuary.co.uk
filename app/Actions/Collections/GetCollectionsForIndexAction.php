<?php

declare(strict_types=1);

namespace App\Actions\Collections;

use App\Models\Collections\Collection;
use App\Resources\Collections\CollectionListCollection;

class GetCollectionsForIndexAction
{
    public function __invoke(int $perPage = 12): CollectionListCollection
    {
        return new CollectionListCollection(
            Collection::query()
                ->with(['media'])
                ->withCount('items')
                ->latest('updated_at')
                ->paginate($perPage)
        );
    }
}
