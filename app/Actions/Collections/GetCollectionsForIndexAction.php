<?php

declare(strict_types=1);

namespace App\Actions\Collections;

use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\ResourceCollections\Collections\CollectionListCollection;
use Illuminate\Database\Eloquent\Builder;

class GetCollectionsForIndexAction
{
    public function handle(int $perPage = 12): CollectionListCollection
    {
        return new CollectionListCollection(
            Collection::query()
                ->with(['media'])
                ->without(['groups', 'groups.items'])
                ->withCount([
                    'items as recipes_count' => fn (Builder $query) => $query->whereHasMorph('item', Recipe::class),
                    'items as blogs_count' => fn (Builder $query) => $query->whereHasMorph('item', Blog::class),
                    'items as eateries_count' => fn (Builder $query) => $query->whereHasMorph('item', [Eatery::class, NationwideBranch::class]),
                ])
                ->latest('updated_at')
                ->paginate($perPage)
        );
    }
}
