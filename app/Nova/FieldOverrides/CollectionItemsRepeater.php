<?php

declare(strict_types=1);

namespace App\Nova\FieldOverrides;

use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionGroup;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CollectionItemsRepeater extends Repeater
{
    /** @var string */
    public $component = 'collection-items';

    protected function resolveAttribute($resource, string $attribute): mixed
    {
        if ($resource instanceof CollectionGroup) {
            $resource->items->loadMissing([
                'item' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                    Blog::class => ['media'],
                    Recipe::class => ['media'],
                    NationwideBranch::class => ['eatery'],
                ]),
            ]);
        }

        return parent::resolveAttribute($resource, $attribute);
    }
}
