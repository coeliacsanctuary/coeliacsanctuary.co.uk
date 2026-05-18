<?php

declare(strict_types=1);

namespace App\Support\Collections;

use App\Models\Collections\CollectionGroupItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @template T of Model
 *
 * @mixin Model
 */
trait CanBeCollected
{
    /** @return MorphMany<CollectionGroupItem, T> */
    public function associatedCollectionGroups(): MorphMany
    {
        return $this->morphMany(CollectionGroupItem::class, 'item');
    }
}
