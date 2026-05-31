<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collections;

use App\Http\Response\Inertia;
use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\Collections\CollectionShowResource;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Response;

class ShowController
{
    public function __invoke(Inertia $inertia, Collection $collection): Response
    {
        $collection->loadMissing(['groups', 'groups.items', 'groups.items.item' => fn(Relation $builder) => $builder->where('live', true)]);

        $collection->groups->each(function (CollectionGroup $group): void {
            $group->items->groupBy('item_type')->each(function (EloquentCollection $items, string $itemType): void {
                $relations = match ($itemType) {
                    Eatery::class => ['item.country', 'item.county', 'item.town', 'item.area', 'item.reviews'],
                    NationwideBranch::class => ['item.country', 'item.county', 'item.town', 'item.area', 'item.reviews', 'item.eatery'],
                    default => ['item.media'],
                };

                $items->loadMissing($relations);
            });
        });

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
